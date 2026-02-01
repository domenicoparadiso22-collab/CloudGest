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
        Schema::create('work_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Chi ha creato il rapporto
        $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Il cliente
        $table->string('number'); // Numero rapporto (es. 2024/001)
        $table->date('date');
        $table->text('notes')->nullable(); // Note visibili
        $table->text('private_notes')->nullable(); // Note interne
        $table->string('status')->default('draft'); // draft, closed, invoiced
        $table->string('customer_signature_path')->nullable(); // Percorso firma
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
