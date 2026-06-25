<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'impuesto')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('impuesto');
            });
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('impuesto')->after('id_impuesto');
        });
    }
};
