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
        Schema::create('cuesheets', function (Blueprint $table) {
            // With primary it fails to create the table in SQLite
            // will need a fix for this in the future
            $table->id();
            $table->string('ride', 10)->nullable();
			$table->string('turn', 30)->nullable();
			$table->string('notes', 200)->nullable();
			$table->float('distance', 8, 1)->nullable();
            $table->integer('completed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuesheets');
    }
};
