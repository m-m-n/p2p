<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 起動時に接続するノードのリストを格納するテーブル
 * 最初は手動で追加する必要があるが他のノードと接続できていれば定期的に他のノードが持つノード情報を取得して追加する
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('node_list', function (Blueprint $table) {
            $table->string("host", 255)->primary();
            $table->integer("port");
            $table->timestamps();
            $table->unique(["host", "port"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_list');
    }
};
