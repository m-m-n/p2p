<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 実際にアップロード可能なファイル一覧を格納するテーブル
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_files', function (Blueprint $table) {
            $table->id();
            $table->string("filename");
            $table->string("sha3_512", 128)->index();
            $table->string("md5", 32)->index();
            $table->integer("bytes");
            $table->timestamps();
            $table->unique(["sha3_512", "md5"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_files');
    }
};
