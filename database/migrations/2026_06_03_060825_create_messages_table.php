<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->morphs('sender'); // Creates sender_type and sender_id (handles both User and Admin)
            $table->text('message');
            $table->string('type')->default('text'); // text, image, file
            $table->string('attachment_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            // $table->index(['sender_type', 'sender_id']);
            $table->index('is_read');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
