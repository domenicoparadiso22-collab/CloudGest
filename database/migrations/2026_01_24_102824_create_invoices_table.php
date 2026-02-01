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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->string('number'); // Numero progressivo
        $table->date('date'); // Data emissione
        $table->date('due_date')->nullable(); // Data scadenza
        $table->string('payment_method')->nullable(); // Bonifico, Ri.Ba, Contanti...
        $table->text('notes')->nullable();
        $table->text('private_notes')->nullable();
        $table->string('status')->default('unpaid'); // unpaid, paid, overdue
        
        // Totali salvati per comodità
        $table->decimal('total_net', 10, 2)->default(0); // Imponibile
        $table->decimal('total_vat', 10, 2)->default(0); // Totale IVA
        $table->decimal('total_gross', 10, 2)->default(0); // Totale Fattura
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
