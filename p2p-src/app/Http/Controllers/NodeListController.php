<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\NodeList;

class NodeListController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(NodeList::getRandomNodes(10));
    }
}
