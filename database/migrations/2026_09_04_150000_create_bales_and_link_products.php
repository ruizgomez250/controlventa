<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->date('purchase_date');
            $table->string('supplier', 120)->nullable();
            $table->string('bale_type', 100)->nullable();
            $table->decimal('purchase_amount', 14, 2);
            $table->decimal('freight_amount', 14, 2)->default(0);
            $table->decimal('other_costs', 14, 2)->default(0);
            $table->unsignedInteger('estimated_quantity')->nullable();
            $table->unsignedInteger('actual_quantity')->nullable();
            $table->unsignedInteger('damaged_quantity')->default(0);
            $table->enum('status', ['pending', 'in_progress', 'finalized'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('bale_id')->nullable()->after('client_uuid')->constrained('bales')->nullOnDelete();
            $table->decimal('bale_unit_cost', 14, 2)->nullable()->after('bale_id');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bale_id');
            $table->dropColumn('bale_unit_cost');
        });
        Schema::dropIfExists('bales');
    }
};
