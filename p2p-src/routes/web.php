<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\NodeListController;

Route::get('/share/{hash}', [FileDownloadController::class, 'download']);
Route::get('/status', [StatusController::class, 'index']);
Route::get('/search', [SearchController::class, 'search']);
Route::get('/node-list', [NodeListController::class, 'index']);
