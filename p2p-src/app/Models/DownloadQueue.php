<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadQueue extends Model
{
    use HasFactory;

    protected $table = "download_queue";

    protected $fillable = [
        "hash",
        "filename",
        "bytes",
    ];
}
