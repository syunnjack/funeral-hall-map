<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 外部データ（OpenStreetMap）から取り込んだ会館と、利用者が投稿した会館を
// 区別できるようにする。再取り込みで重複させないための鍵も兼ねる。
// 葬儀社と斎場・火葬場を分けて出せるよう facility_type も持たせる。
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('facility_type', 20)->nullable()->after('description');
            $table->string('website')->nullable()->after('phone');
            $table->string('opening_hours')->nullable()->after('website');
            $table->string('source', 20)->nullable()->after('likes_count');
            $table->string('source_ref')->nullable()->after('source');

            $table->unique(['source', 'source_ref']);
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropUnique(['source', 'source_ref']);
            $table->dropColumn(['facility_type', 'website', 'opening_hours', 'source', 'source_ref']);
        });
    }
};
