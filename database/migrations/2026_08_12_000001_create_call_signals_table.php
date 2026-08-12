<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 16);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->index();
            $table->index(['booking_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_signals');
    }
};
