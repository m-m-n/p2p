<?php

namespace App\Http\Controllers;

use App\Models\ShareFiles;
use App\Models\Search;
use App\Services\FilePostProcessorService;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * ダウンロード要求に対してファイルを返す
     */
    public function download(string $hash)
    {
        ini_set('max_execution_time', 0);

        $sha3_512 = substr($hash, 0, 128);
        $md5 = substr($hash, 128, 32);
        $data = ShareFiles::where("sha3_512", $sha3_512)->where("md5", $md5)->first();
        if ($data === null) {
            // 上位ノードからファイルをダウンロードする
            $search_record = Search::where("hash", $hash)->inRandomOrder()->first();
            return $this->otherNodeDownload($search_record);
        }

        $file_path = "/share/{$data->filename}";

        if (!file_exists($file_path)) {
            // 404を返す
            abort(404);
        }

        return response()->download($file_path);
    }

    private function otherNodeDownload(Search $search_record)
    {
        $client = new Client();
        $response = $client->get("http://{$search_record->host}:{$search_record->port}/share/{$search_record->hash}", [
            "connect_timeout" => 10,
            'stream' => true,
        ]);

        $file_handle = fopen("/share/tmp/{$search_record->hash}", "w");
        return new StreamedResponse(function () use ($response, $file_handle, $search_record) {
            $body = $response->getBody();
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                fwrite($file_handle, $chunk);
                echo $chunk;
            }
            fclose($file_handle);

            $file_post_processor_service = new FilePostProcessorService();
            $err_msg = $file_post_processor_service->update($search_record->hash, $search_record->filename);
            if ($err_msg !== null) {
                $this->error($err_msg);
                return;
            }
        }, 200, [
            'Content-Type' => $response->getHeaderLine('Content-Type'),
            'Content-Length' => $response->getHeaderLine('Content-Length'),
        ]);
    }
}
