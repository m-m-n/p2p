<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RefreshShare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-share';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '共有ファイル情報更新';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::disk("share")->files("/");
        $data = [];

        $dirname = config("filesystems.disks.share.root");
        foreach ($files as $file) {
            if ($file === ".gitkeep") {
                continue;
            }
            $filePath = "{$dirname}/{$file}";
            $sha3_512 = hash_file("sha3-512", $filePath);
            $md5 = hash_file("md5", $filePath);

            $data[] = [
                "filename" => $file,
                "sha3_512" => $sha3_512,
                "md5" => $md5,
                "bytes" => Storage::disk("share")->size($file),
            ];
        }

        DB::transaction(function () use ($data) {
            DB::table('share_files')->truncate();
            DB::table('share_files')->insert($data);
        });
    }
}
