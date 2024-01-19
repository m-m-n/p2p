<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 自分が返せるファイル一覧を格納するテーブル
 * 検索リクエストを受け取った際にこのテーブルに情報があればファイルを保有していることとして返す
 * 実際にダウンロードリクエストを受け取った際に自分が該当のファイルを保有していなければ記録されているホストにダウンロードリクエストを送る
 * 定期的に他のノードの生存チェックを行い、存在しないノードの情報は削除する
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('search', function (Blueprint $table) {
            $table->string("hash", 160)->index();
            $table->string("filename");
            $table->integer("bytes");
            $table->string("host");
            $table->integer("port");
            $table->timestamps();
            $table->index(["host", "port"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search');
    }
};
