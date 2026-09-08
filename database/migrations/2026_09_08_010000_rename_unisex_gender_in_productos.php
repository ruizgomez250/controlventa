<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productos')->where('gender', 'Unisex')->update(['gender' => 'Ambos sexos']);
    }

    public function down(): void
    {
        DB::table('productos')->where('gender', 'Ambos sexos')->update(['gender' => 'Unisex']);
    }
};
