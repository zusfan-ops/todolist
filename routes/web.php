<?php

use App\Livewire\Auth\Login;
use App\Livewire\Kanban\Board;
use App\Livewire\Log\WorkLogList;
use App\Livewire\Photo\Gallery;
use App\Livewire\Today\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('today');
    Route::get('/kanban', Board::class)->name('kanban');
    Route::get('/log', WorkLogList::class)->name('log');
    Route::get('/photos', Gallery::class)->name('photos');
});
