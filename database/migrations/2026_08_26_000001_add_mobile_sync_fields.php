<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('productos', function (Blueprint $table) { $table->uuid('client_uuid')->nullable()->unique()->after('id'); });
        Schema::table('ventas', function (Blueprint $table) { $table->uuid('client_uuid')->nullable()->unique()->after('id'); $table->string('payment_method', 20)->nullable()->after('tipo_comprobante'); });
    }
    public function down(): void {
        Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn(['client_uuid','payment_method']));
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn('client_uuid'));
    }
};
