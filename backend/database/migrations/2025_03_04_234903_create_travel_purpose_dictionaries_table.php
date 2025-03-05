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
        Schema::create('travel_purpose_dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('travel_purpose', 100);
            $table->string('type', 50)->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_purpose_dictionaries');
    }
};
