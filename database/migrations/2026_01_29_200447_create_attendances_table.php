<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained()->onDelete('cascade');
        $table->date('date');
        $table->dateTime('clock_in')->nullable();
        $table->dateTime('clock_out')->nullable();
        $table->string('location_in')->nullable(); // Coordinate GPS ingresso
        $table->string('location_out')->nullable(); // Coordinate GPS uscita
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
