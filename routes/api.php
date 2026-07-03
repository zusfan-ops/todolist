<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\KanbanColumnController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskPhotoController;
use App\Http\Controllers\Api\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::patch('/projects/{project}', [ProjectController::class, 'update']);

    Route::get('/projects/{project}/columns', [KanbanColumnController::class, 'index']);
    Route::post('/projects/{project}/columns', [KanbanColumnController::class, 'store']);
    Route::patch('/columns/{column}', [KanbanColumnController::class, 'update']);
    Route::delete('/columns/{column}', [KanbanColumnController::class, 'destroy']);

    Route::get('/tasks/today', [TaskController::class, 'today']);
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::post('/tasks/{task}/move', [TaskController::class, 'move']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    Route::post('/tasks/{task}/checklist', [ChecklistItemController::class, 'store']);
    Route::patch('/checklist/{item}', [ChecklistItemController::class, 'update']);
    Route::delete('/checklist/{item}', [ChecklistItemController::class, 'destroy']);

    Route::get('/timer/active', [WorkLogController::class, 'active']);
    Route::post('/tasks/{task}/timer/start', [WorkLogController::class, 'start']);
    Route::post('/timer/{workLog}/stop', [WorkLogController::class, 'stop']);
    Route::post('/tasks/{task}/logs', [WorkLogController::class, 'storeManual']);
    Route::get('/logs', [WorkLogController::class, 'index']);

    Route::post('/tasks/{task}/photos', [TaskPhotoController::class, 'store']);
    Route::get('/tasks/{task}/photos', [TaskPhotoController::class, 'indexForTask']);
    Route::get('/projects/{project}/photos', [TaskPhotoController::class, 'indexForProject']);
    Route::delete('/photos/{photo}', [TaskPhotoController::class, 'destroy']);

    Route::get('/tasks/{task}/activities', [ActivityController::class, 'index']);

    Route::get('/reports/weekly', [ReportController::class, 'weekly']);

    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe']);
    Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);
});
