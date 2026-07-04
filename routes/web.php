<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Kanban\Board;
use App\Livewire\Log\WorkLogList;
use App\Livewire\Photo\Gallery;
use App\Livewire\Today\Dashboard;
use App\Livewire\Todo\Index as TodoIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('today')
        : view('pages.welcome');
})->name('welcome');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('welcome');
})->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('app')->group(function () {
    Route::get('/', Dashboard::class)->name('today');
    Route::get('/todo', TodoIndex::class)->name('todo');
    Route::get('/kanban', Board::class)->name('kanban');
    Route::get('/log', WorkLogList::class)->name('log');
    Route::get('/photos', Gallery::class)->name('photos');
});
