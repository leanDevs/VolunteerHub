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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for filtering
            $table->index('status');
            $table->index('location');
            $table->index(['status', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
