<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('counselors', function (Blueprint $table) {
            $table->text('qualifications')->nullable()->after('specialties');
            $table->text('bio')->nullable()->after('qualifications');
            $table->string('languages')->nullable()->after('bio');
            $table->unsignedTinyInteger('experience_years')->nullable()->after('languages');
        });
    }

    public function down(): void
    {
        Schema::table('counselors', fn (Blueprint $table) => $table->dropColumn(['qualifications','bio','languages','experience_years']));
    }
};
