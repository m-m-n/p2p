<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DownloadQueue;
use App\Models\Search as SearchModel;
use App\Models\ShareFiles;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download {hash}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ファイルダウンロードキューに追加する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $search = SearchModel::where("hash", $this->argument("hash"))->first();
        if ($search === null) {
            $this->error("検索結果が見つかりませんでした");
            return;
        }

        $sha3_512 = substr($search->hash, 0, 128);
        $md5 = substr($search->hash, 128, 32);
        $share = ShareFiles::where("sha3_512", $sha3_512)->where("md5", $md5)->first();
        if ($share !== null) {
            $this->error("ファイルがダウンロード済みです");
            return;
        }

        DownloadQueue::updateOrCreate([
            "hash" => $search->hash,
            "filename" => $search->filename,
            "bytes" => $search->bytes,
        ]);
    }
}
