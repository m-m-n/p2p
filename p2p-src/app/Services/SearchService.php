<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\NodeList;
use App\Models\Search as SearchModel;

class SearchService
{
    /**
     * ノードに検索リクエストを送信する
     *
     * @param string $keyword
     * @param int $ttl
     * @return array
     */
    public function searchRequest(string $keyword, int $ttl = 5): array
    {
        if ($ttl < 1) {
            return [];
        }
        if ($ttl > 5) {
            $ttl = 5;
        }

        // ノードをランダムで10件取得する
        $node_list = NodeList::getRandomNodes(10);
        $search_result = [];
        foreach ($node_list as $node) {
            try {
                $search_result = array_merge($search_result, $this->searchRequestToNode($node, $keyword, $ttl));
            } catch (\Exception $e) {
                // 何もしない
            }
        }
        return $search_result;
    }

    private function searchRequestToNode(NodeList $node, string $keyword, int $ttl): array
    {
        $client = new Client();
        $response = $client->request('GET', "http://{$node->host}:{$node->port}/search", [
            "connect_timeout" => 10,
            'query' => [
                'keyword' => $keyword,
                "ttl" => $ttl,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        foreach (array_keys($data) as $index) {
            $data[$index]['host'] = $node->host;
            $data[$index]['port'] = $node->port;
        }

        return $data;
    }

    /**
     * 検索テーブルを更新する
     *
     * @param array $search_result
     */
    public function updateSearchTable(array $search_result)
    {
        foreach ($search_result as $record) {
            SearchModel::updateOrCreate($record);
        }
    }
}
