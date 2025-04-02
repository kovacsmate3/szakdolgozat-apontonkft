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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('job_number', 50)->unique();
            $table->string('project_name', 75);
            $table->string('location', 100)->nullable();
            $table->string('parcel_identification_number', 100)->nullable();
            $table->dateTime('deadline')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('folyamatban lévő');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->foreign('address_id')
                ->references('id')->on('addresses')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
