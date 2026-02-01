<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('quotes', function (Blueprint $table) {
        // Aggiungiamo le colonne per i totali
        $table->decimal('total_net', 15, 2)->default(0)->after('status');
        $table->decimal('total_vat', 15, 2)->default(0)->after('total_net');
        $table->decimal('total_gross', 15, 2)->default(0)->after('total_vat');
    });
}

public function down()
{
    Schema::table('quotes', function (Blueprint $table) {
        $table->dropColumn(['total_net', 'total_vat', 'total_gross']);
    });
}
};
