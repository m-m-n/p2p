<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Search as SearchModel;
use App\Models\NodeList;
use GuzzleHttp\Client;

class Search extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:search {keyword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ファイル検索';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 検索リクエストを送信する
        $search_result = $this->searchRequest($this->argument('keyword'));
        // 結果をSearchテーブルに保存する
        foreach ($search_result as $record) {
            SearchModel::updateOrCreate($record);
        }

        // Searchテーブルから検索結果を取得する
        // hash値をキーとしてまとめる
        $records = [];
        foreach (SearchModel::where("filename", "like", "%{$this->argument('keyword')}%")->get() as $record) {
            $records[$record->hash] = [
                "filename" => $record->filename,
                "bytes" => number_format($record->bytes),
                "hash" => $record->hash,
            ];
        }

        // 各キーの文字列の最大長を求める
        $strlen_list = [
            "filename" => 0,
            "bytes" => 0,
            "hash" => 0,
        ];
        foreach ($records as $record) {
            foreach ($record as $key => $value) {
                $strlen_list[$key] = max($strlen_list[$key], mb_strwidth($value, "UTF-8"));
            };
        }
        $header = [
            "filename" => "ファイル名",
            "bytes" => "サイズ",
            "hash" => "ハッシュ値",
        ];
        foreach ($header as $key => $value) {
            $strlen_list[$key] = max($strlen_list[$key], mb_strwidth($value, "UTF-8"));
        }

        // MySQLで見たことあるような表を作る
        $this->info("+-" . implode("-+-", array_map(function ($value) {
            return str_repeat("-", $value);
        }, $strlen_list)) . "-+");

        $this->info("| " . implode(" | ", array_map(function ($key, $value) use ($header) {
            return $header[$key] . str_repeat(" ", $value - mb_strwidth($header[$key], "UTF-8"));
        }, array_keys($strlen_list), array_values($strlen_list))) . " |");

        $this->info("+-" . implode("-+-", array_map(function ($value) {
            return str_repeat("-", $value);
        }, $strlen_list)) . "-+");

        // ファイル名とサイズとハッシュ値を表示する
        foreach ($records as $record) {
            $this->info("| " . implode(" | ", array_map(function ($key, $value) use ($record) {
                return $record[$key] . str_repeat(" ", $value - mb_strwidth($record[$key], "UTF-8"));
            }, array_keys($strlen_list), array_values($strlen_list))) . " |");
        }

        $this->info("+-" . implode("-+-", array_map(function ($value) {
            return str_repeat("-", $value);
        }, $strlen_list)) . "-+");
    }

    private function searchRequest(string $keyword): array
    {
        // ノードリストを10件ランダムで取得する
        $nodes = NodeList::getRandomNodes(10);
        // 検索リクエストを送信する
        $search_result = [];
        foreach ($nodes as $node) {
            $search_result = array_merge($search_result, $this->searchRequestToNode($node, $keyword));
        }
        return $search_result;
    }

    private function searchRequestToNode(NodeList $node, string $keyword): array
    {
        $client = new Client();
        $response = $client->request('GET', "http://{$node->host}:{$node->port}/search", [
            'query' => [
                'keyword' => $keyword,
                "ttl" => 1,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        foreach (array_keys($data) as $index) {
            $data[$index]['host'] = $node->host;
            $data[$index]['port'] = $node->port;
        }

        return $data;
    }
}
