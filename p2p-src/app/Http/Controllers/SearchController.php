<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search as SearchModel;
use App\Models\ShareFiles;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $result = $this->retrieveSearchInfo($keyword);

        // 検索結果が0件の場合は上位ノードに検索リクエストを送信する
        if (count($result) === 0) {
            $search_service = new SearchService();
            $search_result = $search_service->searchRequest($keyword, (int) $request->input('ttl') - 1);
            $search_service->updateSearchTable($search_result);
            return response()->json(array_values($this->retrieveSearchInfo($keyword)));
        }

        return response()->json(array_values($result));
    }

    /**
     * 検索結果を取得する
     *
     * @param string $keyword
     */
    private function retrieveSearchInfo(string $keyword): array
    {
        $search_result = SearchModel::where("filename", "like", "%{$keyword}%")->get();
        $share_resut = ShareFiles::where("filename", "like", "%{$keyword}%")->get();

        $search_info = [];
        foreach ($search_result as $record) {
            $search_info[$record->hash] = [
                "filename" => $record->filename,
                "hash" => $record->hash,
                "bytes" => $record->bytes,
            ];
        }
        foreach ($share_resut as $record) {
            $hash = "{$record->sha3_512}{$record->md5}";
            $search_info[$hash] = [
                "filename" => $record->filename,
                "hash" => $hash,
                "bytes" => $record->bytes,
            ];
        }
        return $search_info;
    }
}
