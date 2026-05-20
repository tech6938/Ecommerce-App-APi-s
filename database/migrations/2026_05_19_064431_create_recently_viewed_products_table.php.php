<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recently_viewed_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->timestamps();

            // One user can view same product multiple times, but we'll update timestamp
            $table->unique(['user_id', 'product_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('recently_viewed_products');
    }
};
