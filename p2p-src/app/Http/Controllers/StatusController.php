<?php

namespace App\Http\Controllers;

use App\Models\NodeList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $host = $request->ip();
        $port = $request->input('port');

        NodeList::updateOrCreate([
            'host' => $host,
            'port' => $port,
        ]);

        return response()->json([
            'status' => 'OK',
        ]);
    }
}
