<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bale_product_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bale_id')->constrained('bales')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->timestamps();
            $table->unique(['bale_id', 'producto_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('bale_product_items'); }
};
