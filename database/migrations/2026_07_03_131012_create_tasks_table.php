<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kanban_column_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->integer('estimate_minutes')->unsigned()->nullable();
            $table->integer('position')->unsigned();
            $table->tinyInteger('progress_cached')->unsigned()->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->char('client_uuid', 36)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'kanban_column_id', 'position']);
            $table->index('due_date');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
