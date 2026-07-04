<?php

namespace App\Http\Controllers;

use App\Models\User;

class SharedTodoController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('share_token', $token)->firstOrFail();
        $todos = $user->simpleTodos()->orderBy('position')->get();
        $userName = $user->name;

        return view('pages.shared-todos', compact('todos', 'userName'));
    }
}