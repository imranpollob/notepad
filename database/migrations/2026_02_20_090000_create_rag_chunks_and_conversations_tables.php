<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRagChunksAndConversationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('source_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('content');
            $table->unsignedInteger('token_count')->default(0);
            $table->json('embedding')->nullable();
            $table->string('embedding_model', 100)->nullable();
            $table->timestamp('embedded_at')->nullable();
            $table->timestamps();

            $table->unique(['source_id', 'chunk_index']);
            $table->index('source_id');
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notebook_id')->constrained('notebooks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['notebook_id', 'updated_at']);
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('message');
            $table->json('metadata')->nullable();
            $table->unsignedInteger('token_usage')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('source_chunks');
    }
}
