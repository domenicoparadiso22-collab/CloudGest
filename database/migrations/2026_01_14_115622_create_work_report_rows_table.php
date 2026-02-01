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
        Schema::create('work_report_rows', function (Blueprint $table) {
        $table->id();
        $table->foreignId('work_report_id')->constrained()->onDelete('cascade');
        $table->string('description');
        $table->decimal('quantity', 10, 2);
        $table->string('unit')->default('pz');
        $table->decimal('price', 10, 2);
        $table->decimal('total', 10, 2); // Qta * Prezzo
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_report_rows');
    }
};
