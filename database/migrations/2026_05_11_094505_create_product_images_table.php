<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->string('image');          // path: products/abc.jpg
            $table->unsignedSmallInteger('sort_order')->default(0); // 0 = main/thumbnail
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};