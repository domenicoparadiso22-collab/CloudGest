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
        // Se employee_id è NULL, il messaggio è per tutti. Se c'è l'ID, è privato.
        $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade')->after('user_id');
        
        // Per inviare posizioni (es. "41.123,16.456")
        $table->string('target_location')->nullable()->after('message'); 
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
