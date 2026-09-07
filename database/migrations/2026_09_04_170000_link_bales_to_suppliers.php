<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('bales', function (Blueprint $table) { $table->foreignId('supplier_id')->nullable()->after('purchase_date')->constrained('proveedores')->nullOnDelete(); }); }
    public function down(): void { Schema::table('bales', fn (Blueprint $table) => $table->dropConstrainedForeignId('supplier_id')); }
};
