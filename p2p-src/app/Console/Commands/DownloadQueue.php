<?php

namespace App\Console\Commands;

use App\Models\DownloadQueue as DownloadQueueModel;
use App\Models\Search as SearchModel;
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
                        "sink" => "/share/tmp/{$item->hash}",
                    ]);
                    $request->then(function ($response) use ($item) {
                        // ダウンロードが成功した場合の処理
                        $filepath = "/share/tmp/{$item->hash}";
                        if (!Storage::disk("share")->exists("/tmp/{$item->hash}")) {
                            $this->error("ファイルが存在しません: {$filepath}");
                            return;
                        }
                        $this->info("ファイルをダウンロードしました: {$filepath}");
                        $sha3_512 = hash_file('sha3-512', $filepath);
                        $md5 = hash_file('md5', $filepath);
                        $hash = "{$sha3_512}{$md5}";
                        if ($item->hash !== $hash) {
                            $this->info("ファイルのハッシュ値が一致しませんでした");
                            unlink($filepath);
                            return;
                        }

                        // ファイルを/shareに移動する
                        if (!Storage::disk("share")->move("/tmp/{$item->hash}", "/{$item->filename}")) {
                            $this->error("ファイルの移動に失敗しました: {$filepath}");
                            return;
                        }
                        // ShareFilesをアップデートする
                        Artisan::call('app:refresh-share');
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
