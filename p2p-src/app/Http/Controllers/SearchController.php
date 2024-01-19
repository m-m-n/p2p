<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Search as SearchModel;
use App\Models\ShareFiles;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $search_result = SearchModel::where("filename", "like", "%{$keyword}%")->get();
        $share_resut = ShareFiles::where("filename", "like", "%{$keyword}%")->get();

        $result = [];
        foreach ($search_result as $record) {
            $result[$record->hash] = [
                "filename" => $record->filename,
                "hash" => $record->hash,
                "bytes" => $record->bytes,
            ];
        }
        foreach ($share_resut as $record) {
            $hash = "{$record->sha3_512}{$record->md5}";
            $result[$hash] = [
                "filename" => $record->filename,
                "hash" => $hash,
                "bytes" => $record->bytes,
            ];
        }

        return response()->json(array_values($result));
    }
}
