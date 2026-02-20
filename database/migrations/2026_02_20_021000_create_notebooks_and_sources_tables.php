<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotebooksAndSourcesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('visibility', 20)->default('private');
            $table->string('share_token', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notebook_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('note_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->string('source_type', 20);
            $table->string('title')->nullable();
            $table->text('origin_url')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['notebook_id', 'status']);
            $table->index('source_type');
        });

        Schema::create('source_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('disk', 50)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->index('source_id');
        });

        Schema::create('source_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->longText('content_text')->nullable();
            $table->longText('content_html')->nullable();
            $table->string('language', 16)->nullable();
            $table->unsignedInteger('word_count')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();

            $table->unique('source_id');
        });

        Schema::create('source_ingestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('sources')->cascadeOnDelete();
            $table->string('job_type', 30)->default('extract');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempt')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('source_ingestions');
        Schema::dropIfExists('source_contents');
        Schema::dropIfExists('source_files');
        Schema::dropIfExists('sources');
        Schema::dropIfExists('notebooks');
    }
}
