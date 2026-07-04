<?php

namespace App\Livewire\Todo;

use App\Models\SimpleTodo;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $newBody = '';

    public function add(): void
    {
        $this->validate(['newBody' => ['required', 'string', 'max:300']]);

        $position = (int) (auth()->user()->simpleTodos()->max('position') ?? 0) + 1;

        auth()->user()->simpleTodos()->create([
            'body' => $this->newBody,
            'position' => $position,
        ]);

        $this->newBody = '';
    }

    public function toggle(int $id): void
    {
        $todo = auth()->user()->simpleTodos()->findOrFail($id);

        $todo->update(['is_done' => ! $todo->is_done]);
    }

    public function delete(int $id): void
    {
        auth()->user()->simpleTodos()->findOrFail($id)->delete();
    }

    public function clearCompleted(): void
    {
        auth()->user()->simpleTodos()->where('is_done', true)->delete();
    }

    public function render()
    {
        $todos = auth()->user()->simpleTodos()->orderBy('position')->get();

        return view('livewire.todo.index', [
            'todos' => $todos,
            'doneCount' => $todos->where('is_done', true)->count(),
        ]);
    }
}
