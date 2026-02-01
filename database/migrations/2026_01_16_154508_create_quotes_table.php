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
        Schema::create('quotes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('client_id')->constrained()->onDelete('cascade');
        $table->string('number');
        $table->date('date');
        $table->date('valid_until')->nullable(); // Validità offerta
        $table->text('notes')->nullable();
        $table->text('private_notes')->nullable();
        $table->string('status')->default('draft'); // draft, sent, accepted, rejected
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
