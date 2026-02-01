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
    Schema::create('invoice_rows', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
        $table->string('description');
        $table->decimal('quantity', 10, 2);
        $table->string('unit')->default('pz');
        $table->decimal('price', 10, 2); // Prezzo unitario
        $table->integer('vat_rate')->default(22); // Aliquota IVA (es. 22)
        $table->decimal('total', 10, 2); // Totale riga (Imponibile)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_rows');
    }
};
