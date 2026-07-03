<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['before', 'progress', 'after', 'proof'])->default('progress');
            $table->string('disk', 20)->default('public');
            $table->string('path', 255);
            $table->string('thumb_path', 255);
            $table->string('caption', 300)->nullable();
            $table->char('sha256', 64);
            $table->integer('size_bytes')->unsigned();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->char('client_uuid', 36)->unique();
            $table->timestamps();

            $table->index(['task_id', 'type']);
            $table->index('sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_photos');
    }
};
