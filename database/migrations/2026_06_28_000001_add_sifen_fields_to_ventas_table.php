<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('sifen_cdc', 44)->nullable()->after('estado');
            $table->string('sifen_cde', 40)->nullable()->after('sifen_cdc');
            $table->string('sifen_estado', 20)->nullable()->after('sifen_cde');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['sifen_cdc', 'sifen_cde', 'sifen_estado']);
        });
    }
};
