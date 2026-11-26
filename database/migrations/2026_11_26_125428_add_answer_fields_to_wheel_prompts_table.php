<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prompts are multiple-choice: the options, which one is right, and the message the host
     * reads out after revealing it. All nullable so an open question with no options still works.
     */
    public function up(): void
    {
        Schema::table('wheel_prompts', function (Blueprint $table) {
            $table->json('options')->nullable()->after('body');
            $table->char('answer', 1)->nullable()->after('options');
            $table->text('key_message')->nullable()->after('answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_prompts', function (Blueprint $table) {
            $table->dropColumn(['options', 'answer', 'key_message']);
        });
    }
};
