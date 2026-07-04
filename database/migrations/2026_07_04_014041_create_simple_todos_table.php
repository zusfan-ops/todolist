<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flat todo list — deliberately separate from projects/tasks/kanban.
     * A simpler mode for users who just want a plain checklist.
     */
    public function up(): void
    {
        Schema::create('simple_todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('body', 300);
            $table->boolean('is_done')->default(false);
            $table->integer('position')->unsigned();
            $table->timestamps();

            $table->index(['user_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simple_todos');
    }
};
