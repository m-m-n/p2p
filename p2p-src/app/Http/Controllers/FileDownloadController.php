<?php

namespace App\Http\Controllers;

use App\Models\ShareFiles;
use App\Models\Search;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * ダウンロード要求に対してファイルを返す
     */
    public function download(string $hash)
    {
        $sha3_512 = substr($hash, 0, 128);
        $md5 = substr($hash, 128, 32);
        $data = ShareFiles::where("sha3_512", $sha3_512)->where("md5", $md5)->first();
        if ($data === null) {
            // 上位ノードからファイルをダウンロードする
            $other_node = Search::where("hash", $hash)->inRandomOrder()->first();
            return $this->otherNodeDownload($other_node, $hash);
        }

        $file_path = "/share/{$data->filename}";

        if (!file_exists($file_path)) {
            // 404を返す
            abort(404);
        }

        return response()->download($file_path);
    }

    private function otherNodeDownload(Search $other_node, string $hash)
    {
        $client = new Client();
        $response = $client->get("http://{$other_node->host}:{$other_node->port}/share/{$hash}", [
            "connect_timeout" => 10,
            'stream' => true,
        ]);

        $file_handle = fopen("/share/tmp/{$hash}", "w");
        return new StreamedResponse(function () use ($response, $file_handle) {
            $body = $response->getBody();
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                fwrite($file_handle, $chunk);
                echo $chunk;
            }
            fclose($file_handle);
        }, 200, [
            'Content-Type' => $response->getHeaderLine('Content-Type'),
            'Content-Length' => $response->getHeaderLine('Content-Length'),
        ]);
    }
}
