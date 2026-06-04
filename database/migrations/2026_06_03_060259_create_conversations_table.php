<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->unique(); // Public ID for API
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Customer (from your users table)
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null'); // Admin (from your admins table)
            $table->string('status')->default('pending'); // pending, active, closed
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['admin_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};
