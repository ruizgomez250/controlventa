<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE proveedores MODIFY celular VARCHAR(25) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE proveedores MODIFY celular VARCHAR(12) NULL');
    }
};
