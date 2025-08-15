<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approvers', function (Blueprint $table) {
            $table->string('updated_userid')->nullable()->after('level');
            $table->string('updated_username')->nullable()->after('updated_userid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvers', function (Blueprint $table) {
            $table->dropColumn(['updated_userid', 'updated_username']);
        });
    }
};
