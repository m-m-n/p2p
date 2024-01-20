<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class FilePostProcessorService
{
    public function update(string $hash, string $to_filename): ?string
    {
        $filepath = "/share/tmp/{$hash}";
        if (!Storage::disk("share")->exists("/tmp/{$hash}")) {
            return "ファイルが存在しません: {$filepath}";
        }
        $sha3_512 = hash_file('sha3-512', $filepath);
        $md5 = hash_file('md5', $filepath);
        $calc_hash = "{$sha3_512}{$md5}";
        if ($hash !== $calc_hash) {
            unlink($filepath);
            return "ファイルのハッシュ値が一致しませんでした";
        }

        // ファイルを/shareに移動する
        if (!Storage::disk("share")->move("/tmp/{$hash}", "/{$to_filename}")) {
            return "ファイルの移動に失敗しました: {$filepath}";
        }
        // ShareFilesをアップデートする
        Artisan::call('app:refresh-share');

        return null;
    }
}
