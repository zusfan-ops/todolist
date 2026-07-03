<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('body', 300);
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->integer('position')->unsigned();
            $table->char('client_uuid', 36)->unique();
            $table->timestamps();

            $table->index(['task_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
