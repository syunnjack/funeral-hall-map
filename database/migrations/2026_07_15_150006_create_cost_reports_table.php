<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->string('funeral_type', 20)->nullable();
            $table->unsignedSmallInteger('attendee_count')->nullable();
            $table->unsignedInteger('total_cost');
            $table->text('comment')->nullable();
            $table->string('nickname', 30)->default('匿名');
            $table->string('ip_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_reports');
    }
};
