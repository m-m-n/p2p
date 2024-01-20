<?php

namespace App\Console\Commands;

use App\Models\DownloadQueue as DownloadQueueModel;
use App\Models\Search as SearchModel;
use App\Services\FilePostProcessorService;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class DownloadQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = DownloadQueueModel::inRandomOrder()->limit(12)->get();

        $client = new Client();

        $requests = function ($client, $queue) {
            foreach ($queue as $item) {
                yield function () use ($item, $client) {
                    $host = SearchModel::where("hash", $item->hash)->inRandomOrder()->first();
                    $request = $client->getAsync("http://{$host->host}:{$host->port}/share/{$item->hash}", [
                        "connect_timeout" => 10,
                        "sink" => "/share/tmp/{$item->hash}",
                    ]);
                    $request->then(function ($response) use ($item) {
                        // ダウンロードが成功した場合の処理
                        $file_post_processor_service = new FilePostProcessorService();
                        $err_msg = $file_post_processor_service->update($item->hash, $item->filename);
                        if ($err_msg !== null) {
                            $this->error($err_msg);
                            return;
                        }
                        // DownloadQueueを削除する
                        DownloadQueueModel::where("hash", $item->hash)->delete();
                    }, function ($exception) use ($item) {
                        // ダウンロードが失敗した場合の処理
                        $this->error("ファイルのダウンロードに失敗しました: {$exception->getMessage()}");
                        // とりあえず失敗したらファイルを削除しておく
                        // TODO: レンジリクエストでレジュームできるようにする
                        $filepath = "/share/tmp/{$item->hash}";
                        unlink($filepath);
                    });
                    return $request;
                };
            }
        };
        $pool = new Pool($client, $requests($client, $queue), [
            'concurrency' => 3,
        ]);

        $pool->promise()->wait();
    }
}
