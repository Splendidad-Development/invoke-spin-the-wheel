<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Challenges have a short name ("Sing It!") shown in the dialog title next to their number.
     */
    public function up(): void
    {
        Schema::table('wheel_prompts', function (Blueprint $table) {
            $table->string('name', 60)->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_prompts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
