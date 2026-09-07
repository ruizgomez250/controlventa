<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('clothing_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('garment_type_id')->nullable()->after('id_categoria')->constrained('garment_types')->nullOnDelete();
            $table->foreignId('clothing_size_id')->nullable()->after('garment_type_id')->constrained('clothing_sizes')->nullOnDelete();
            $table->string('brand', 80)->nullable()->after('clothing_size_id');
            $table->string('color', 60)->nullable()->after('brand');
            $table->string('age_group', 40)->nullable()->after('color');
            $table->string('collection', 100)->nullable()->after('age_group');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('garment_type_id');
            $table->dropConstrainedForeignId('clothing_size_id');
            $table->dropColumn(['brand', 'color', 'age_group', 'collection']);
        });
        Schema::dropIfExists('clothing_sizes');
        Schema::dropIfExists('garment_types');
    }
};
