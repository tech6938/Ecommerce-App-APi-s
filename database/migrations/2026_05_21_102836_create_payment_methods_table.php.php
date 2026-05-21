<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Paytm, Stripe, COD
            $table->string('code')->unique(); // paytm, stripe, cod
            $table->string('type'); // cod, online
            $table->text('description')->nullable();

            // Gateway Credentials
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('merchant_key')->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('public_key')->nullable();
            $table->string('private_key')->nullable();
            $table->string('callback_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('environment')->default('sandbox'); // sandbox, production
            $table->json('extra_credentials')->nullable(); // For any additional fields

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};
