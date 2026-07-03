<?php

namespace App\Exceptions;

use App\Models\Task;
use Exception;

class ChecklistIncompleteException extends Exception
{
    public function __construct(public Task $task)
    {
        $done = $task->checklistItems()->where('is_done', true)->count();
        $total = $task->checklistItems()->count();

        parent::__construct("Checklist belum selesai ({$done}/{$total}). Tetap tandai selesai?");
    }
}
