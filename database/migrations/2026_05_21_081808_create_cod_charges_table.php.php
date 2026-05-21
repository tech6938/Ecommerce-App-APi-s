<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cod_charges', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->decimal('max_order_amount', 15, 2)->nullable();
            $table->decimal('charge_amount', 15, 2)->default(0);
            $table->enum('charge_type', ['fixed', 'percentage'])->default('fixed');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index(['min_order_amount', 'max_order_amount']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cod_charges');
    }
};
