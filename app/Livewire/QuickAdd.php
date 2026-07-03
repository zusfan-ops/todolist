<?php

namespace App\Livewire;

use Livewire\Component;

class QuickAdd extends Component
{
    public function render()
    {
        $projects = auth()->user()->projects()->active()->orderBy('position')->get();

        return view('livewire.quick-add', [
            'projects' => $projects,
            'defaultProjectId' => $projects->first()?->id,
        ]);
    }
}
