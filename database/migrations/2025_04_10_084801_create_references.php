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
        Schema::create('referances', function (Blueprint $table) {
            $table->id();
            $table->string('userid')->unique();
            $table->string('HN')->unique();
            $table->string('refID');
            $table->boolean('passport')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referances');
    }
};
