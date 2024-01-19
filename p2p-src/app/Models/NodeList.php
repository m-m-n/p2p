<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class NodeList extends Model
{
    use HasFactory;

    protected $table = "node_list";
    protected $fillable = ["host", "port"];

    public static function getRandomNodes(int $limit = 10): Collection
    {
        return self::select("host", "port")->inRandomOrder()->limit($limit)->get();
    }
}
