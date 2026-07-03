<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('slug', 60);
            $table->smallInteger('position')->unsigned();
            $table->smallInteger('wip_limit')->unsigned()->nullable();
            $table->boolean('is_done_column')->default(false);
            $table->tinyInteger('fallback_progress')->unsigned()->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'slug']);
            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kanban_columns');
    }
};
