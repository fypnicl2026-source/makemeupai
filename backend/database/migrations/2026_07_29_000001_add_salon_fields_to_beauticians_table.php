<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beauticians', function (Blueprint $table) {
            $table->string('salon_name')->nullable()->after('name');
            $table->string('area')->nullable()->after('city');
            $table->string('gender_focus', 20)->default('female')->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('beauticians', function (Blueprint $table) {
            $table->dropColumn(['salon_name', 'area', 'gender_focus']);
        });
    }
};
