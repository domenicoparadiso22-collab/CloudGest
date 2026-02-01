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
    Schema::table('notices', function (Blueprint $table) {
        $table->string('target_email')->nullable()->after('target_location');
        $table->string('target_phone')->nullable()->after('target_email');
        // Rendiamo il messaggio nullable se non lo era (per sicurezza)
        $table->text('message')->nullable()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            //
        });
    }
};
