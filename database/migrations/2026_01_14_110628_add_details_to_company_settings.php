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
    Schema::table('company_settings', function (Blueprint $table) {
        $table->string('subtitle')->nullable()->after('company_name'); // Sottotitolo
        $table->string('fiscal_code')->nullable()->after('vat_number'); // Codice Fiscale
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            //
        });
    }
};
