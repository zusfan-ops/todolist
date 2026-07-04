<?php

namespace App\Livewire\Note;

use App\Models\Note;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $filterCategory = '';
    #[Url]
    public ?string $filterDate = null;

    public bool $showForm = false;
    public ?int $editingNoteId = null;
    public string $formTitle = '';
    public string $formContent = '';
    public string $formCategory = 'umum';
    public string $formDate = '';
    public $formAttachments = [];

    public array $categories = ['umum', 'ide', 'meeting', 'tugas', 'pribadi'];

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:255'],
            'formContent' => ['nullable', 'string'],
            'formCategory' => ['required', 'string', 'in:' . implode(',', $this->categories)],
            'formDate' => ['required', 'date'],
            'formAttachments.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function openCreateForm(?string $date = null): void
    {
        $this->resetForm();
        $this->formDate = $date ?? now()->toDateString();
        $this->showForm = true;
        $this->editingNoteId = null;
    }

    public function openEditForm(int $noteId): void
    {
        $note = Note::where('user_id', auth()->id())->findOrFail($noteId);
        $this->editingNoteId = $note->id;
        $this->formTitle = $note->title;
        $this->formContent = $note->content ?? '';
        $this->formCategory = $note->category;
        $this->formDate = $note->date->toDateString();
        $this->formAttachments = [];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->formTitle,
            'content' => $this->formContent,
            'category' => $this->formCategory,
            'date' => $this->formDate,
        ];

        if ($this->editingNoteId) {
            $note = Note::where('user_id', auth()->id())->findOrFail($this->editingNoteId);
            $note->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $note = Note::create($data);
        }

        foreach ($this->formAttachments as $upload) {
            $path = $upload->store('notes/' . $note->id, 'public');
            $note->attachments()->create([
                'file_path' => $path,
                'file_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getMimeType(),
                'file_size' => $upload->getSize(),
            ]);
        }

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('note-saved');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = \App\Models\NoteAttachment::findOrFail($attachmentId);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    public function deleteNote(int $noteId): void
    {
        $note = Note::where('user_id', auth()->id())->findOrFail($noteId);
        foreach ($note->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
        }
        $note->attachments()->delete();
        $note->delete();
    }

    public function setFilterCategory(string $category): void
    {
        $this->filterCategory = $category === $this->filterCategory ? '' : $category;
    }

    private function resetForm(): void
    {
        $this->editingNoteId = null;
        $this->formTitle = '';
        $this->formContent = '';
        $this->formCategory = 'umum';
        $this->formDate = now()->toDateString();
        $this->formAttachments = [];
    }

    public function render()
    {
        $query = Note::with('attachments')
            ->where('user_id', auth()->id());

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterDate) {
            $query->whereDate('date', $this->filterDate);
        }

        $notes = $query->orderByDesc('date')->orderByDesc('created_at')->get();
        $notesByDate = $notes->groupBy(fn ($n) => $n->date->toDateString());

        $categoryCounts = Note::where('user_id', auth()->id())
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('livewire.note.index', [
            'notes' => $notes,
            'notesByDate' => $notesByDate,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}