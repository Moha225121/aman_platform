<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('bookings',fn(Blueprint $table)=>$table->text('location_url')->nullable()->after('meeting_url')); }
    public function down(): void { Schema::table('bookings',fn(Blueprint $table)=>$table->dropColumn('location_url')); }
};
