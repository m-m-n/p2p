<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * download_queueテーブルの作成と削除
 * ダウンロードしたいファイルの情報を格納する
 * ハッシュ値をキーにダウンロードを行う
 * 登録時のファイル名でダウンロードが完了した際にファイル名を変更する
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('download_queue', function (Blueprint $table) {
            $table->string("hash", 128 + 32)->primary();
            $table->string("filename");
            $table->integer("bytes");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_queue');
    }
};
