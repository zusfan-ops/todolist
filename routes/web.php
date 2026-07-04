<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Calendar\Index as CalendarIndex;
use App\Livewire\Kanban\Board;
use App\Livewire\Note\Index as NoteIndex;
use App\Livewire\Log\WorkLogList;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Photo\Gallery;
use App\Livewire\Staff\AcceptInvite;
use App\Livewire\Staff\Index as StaffIndex;
use App\Livewire\Today\Dashboard;
use App\Livewire\Todo\Index as TodoIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('today')
        : view('pages.welcome');
})->name('welcome');

Route::get('/share/{token}', [App\Http\Controllers\SharedTodoController::class, 'show'])->name('shared.todos');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/invite/{token}', AcceptInvite::class)->name('invite.accept');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('welcome');
})->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('app')->group(function () {
    Route::get('/guide', function () {
        return view('pages.guide');
    })->name('guide');

    Route::post('/onboarding/done', function (Request $request) {
        $request->user()->update(['seen_onboarding' => true]);
        return response()->json(['ok' => true]);
    })->name('onboarding.done');

    Route::get('/', Dashboard::class)->name('today');
    Route::get('/todo', TodoIndex::class)->name('todo');
    Route::get('/calendar', CalendarIndex::class)->name('calendar');
    Route::get('/kanban', Board::class)->name('kanban');
    Route::get('/log', WorkLogList::class)->name('log');
    Route::get('/analytics', AnalyticsIndex::class)->name('analytics');
    Route::get('/photos', Gallery::class)->name('photos');
    Route::get('/notes', NoteIndex::class)->name('notes');
    Route::get('/settings', SettingsIndex::class)->name('settings');
    Route::get('/staff', StaffIndex::class)->name('staff');
});
