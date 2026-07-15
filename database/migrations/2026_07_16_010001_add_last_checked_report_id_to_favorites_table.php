<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            // created_at(秒精度)の比較では、同一会館に複数のCostReportが同一秒内に
            // 投稿されると後続の投稿を永久に検知できなくなる恐れがあるため、常に厳密単調増加する
            // cost_reports.idを検知カーソルとして使う方式に変更する。
            $table->unsignedBigInteger('last_checked_report_id')->nullable()->after('venue_id');
            $table->dropColumn('last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->timestamp('last_checked_at')->nullable();
            $table->dropColumn('last_checked_report_id');
        });
    }
};
