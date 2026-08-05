<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
            $table->string('alias')->nullable()->after('username');
            $table->string('role')->default('user')->after('password');
            $table->timestamp('policy_accepted_at')->nullable();
            $table->string('policy_version')->nullable();
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('description');
            $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('support_programs', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->text('description')->nullable();
            $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('counselors', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('title');
            $table->text('specialties')->nullable(); $table->decimal('rating', 2, 1)->default(5);
            $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('support_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); $table->text('note')->nullable();
            $table->timestamp('scheduled_at')->nullable(); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bookings'); Schema::dropIfExists('counselors');
        Schema::dropIfExists('support_programs'); Schema::dropIfExists('services');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['username','alias','role','policy_accepted_at','policy_version']));
    }
};
