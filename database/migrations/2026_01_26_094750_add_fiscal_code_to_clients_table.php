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
    Schema::table('clients', function (Blueprint $table) {
        // Aggiungiamo il CF dopo la P.IVA, nullable (perché non sempre c'è)
        $table->string('fiscal_code', 16)->nullable()->after('vat_number');
    });
}

public function down(): void
{
    Schema::table('clients', function (Blueprint $table) {
        $table->dropColumn('fiscal_code');
    });
}
};
