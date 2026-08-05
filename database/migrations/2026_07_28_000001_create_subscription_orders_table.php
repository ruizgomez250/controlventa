<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('provider', 50);
            $table->string('external_id')->unique();
            $table->string('customer_email');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('PYG');
            $table->string('status', 20)->default('pending')->index();
            $table->string('activation_token_hash', 64)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('activation_expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');
    }
};
