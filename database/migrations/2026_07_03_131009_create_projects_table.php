<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->char('color', 7);
            $table->string('icon', 50)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->smallInteger('position')->unsigned()->default(0);
            $table->char('client_uuid', 36)->unique();
            $table->timestamps();

            $table->index(['user_id', 'status', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
