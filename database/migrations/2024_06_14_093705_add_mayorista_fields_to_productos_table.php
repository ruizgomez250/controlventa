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
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('pmayorista', 10, 0)->default(0)->nullable()->after('pventa');
            $table->integer('cmayorista')->default(0)->nullable()->after('pmayorista');
            $table->decimal('dmayorista', 10, 0)->default(0)->nullable()->after('cmayorista');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('pmayorista');
            $table->dropColumn('cmayorista');
            $table->dropColumn('dmayorista');
        });
    }
};
