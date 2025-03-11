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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->date('work_date');
            $table->time('hours');
            $table->text('note')->nullable();
            $table->string('work_type', 50);
            $table->unsignedBigInteger('leaverequest_id')->nullable();
            $table->foreign('leaverequest_id')->references('id')->on('leave_requests')->onDelete('cascade');
            $table->unsignedBigInteger('overtimerequest_id')->nullable();
            $table->foreign('overtimerequest_id')->references('id')->on('overtime_requests')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
