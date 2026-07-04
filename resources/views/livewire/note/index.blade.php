<div class="px-5 pt-5 space-y-5">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 dark:text-white text-base uppercase tracking-wider">Catatan</h2>
        <button wire:click="openCreateForm" class="bg-vest-500 text-ink-900 font-disp font-bold text-sm px-4 py-2 rounded-lg">+ Catatan</button>
    </div>

    <div class="flex gap-2 overflow-x-auto no-scrollbar">
        @foreach ($categories as $cat)
            <button wire:click="setFilterCategory('{{ $cat }}')"
                    class="text-xs font-disp font-bold px-3 py-1.5 rounded-full whitespace-nowrap border
                           {{ $filterCategory === $cat ? 'bg-ink-900 dark:bg-white text-white dark:text-ink-900 border-ink-900 dark:border-white' : 'bg-white dark:bg-ink-700 text-ink-700 dark:text-ink-300 border-ink-100 dark:border-ink-500' }}">
                {{ ucfirst($cat) }}
                @if (isset($categoryCounts[$cat]))
                    <span class="ml-1 opacity-60">({{ $categoryCounts[$cat] }})</span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($notesByDate as $date => $dateNotes)
            <div>
                <h3 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-500 dark:text-ink-300 mb-2">
                    {{ now()->parse($date)->translatedFormat('l, j F Y') }}
                </h3>
                <div class="space-y-2">
                    @foreach ($dateNotes as $note)
                        <div wire:key="note-{{ $note->id }}" class="bg-white dark:bg-ink-700 rounded-xl border border-ink-100 dark:border-ink-500 p-4">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[10px] font-disp font-bold px-2 py-0.5 rounded-full shrink-0
                                        {{ $note->category === 'umum' ? 'bg-ink-100 dark:bg-ink-500 text-ink-700 dark:text-ink-200' : '' }}
                                        {{ $note->category === 'ide' ? 'bg-vest-100 dark:bg-vest-500/20 text-vest-600' : '' }}
                                        {{ $note->category === 'meeting' ? 'bg-leaf-100 dark:bg-leaf-500/20 text-leaf-600' : '' }}
                                        {{ $note->category === 'tugas' ? 'bg-brick-100 dark:bg-brick-500/20 text-brick-600' : '' }}
                                        {{ $note->category === 'pribadi' ? 'bg-ink-900/10 dark:bg-white/10 text-ink-700 dark:text-ink-200' : '' }}">
                                        {{ ucfirst($note->category) }}
                                    </span>
                                    <span class="font-disp font-bold text-sm text-ink-900 dark:text-white truncate">{{ $note->title }}</span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button wire:click="openEditForm({{ $note->id }})" class="text-ink-300 dark:text-ink-400 hover:text-ink-500 text-sm leading-none px-1">✎</button>
                                    <button wire:click="deleteNote({{ $note->id }})" wire:confirm="Hapus catatan ini?" class="text-ink-300 dark:text-ink-400 hover:text-brick-500 text-lg leading-none px-1">&times;</button>
                                </div>
                            </div>
                            @if ($note->content)
                                <p class="text-sm text-ink-500 dark:text-ink-300 whitespace-pre-wrap">{{ $note->content }}</p>
                            @endif
                            @if ($note->attachments->count())
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach ($note->attachments as $att)
                                        <div class="relative group">
                                            @if ($att->isImage())
                                                <img src="{{ $att->url() }}" alt="{{ $att->file_name }}" class="w-16 h-16 rounded-lg object-cover">
                                            @else
                                                <a href="{{ $att->url() }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-mono bg-ink-100 dark:bg-ink-500 text-ink-700 dark:text-ink-200 px-2 py-1 rounded-lg">
                                                    📎 {{ Str::limit($att->file_name, 20) }}
                                                </a>
                                            @endif
                                            <button wire:click="deleteAttachment({{ $att->id }})" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-brick-500 text-white text-[8px] leading-none grid place-items-center opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-500 dark:text-ink-300 bg-white dark:bg-ink-700 border border-dashed border-ink-300 dark:border-ink-500 rounded-xl p-6 text-center">
                Belum ada catatan. Tekan "+ Catatan" untuk membuat yang pertama.
            </p>
        @endforelse
    </div>

    <div x-show="$wire.showForm" x-cloak @click.self="$wire.showForm = false" class="fixed inset-0 bg-ink-900/50 flex items-end z-30">
        <div class="bg-white dark:bg-ink-700 w-full rounded-t-3xl max-h-[85%] overflow-y-auto no-scrollbar p-5">
            <div class="w-12 h-1.5 bg-ink-100 dark:bg-ink-500 rounded-full mx-auto mb-4"></div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-disp font-bold text-lg text-ink-900 dark:text-white" x-text="$wire.editingNoteId ? 'Edit Catatan' : 'Catatan Baru'"></h3>
                <button @click="$wire.showForm = false" class="text-ink-300 dark:text-ink-400 text-xl leading-none">&times;</button>
            </div>

            <form wire:submit="save" class="space-y-3">
                <div>
                    <input wire:model="formTitle" placeholder="Judul catatan" autofocus
                           class="w-full bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3 text-base text-ink-900 dark:text-white focus:outline-none focus:border-vest-500 placeholder:text-ink-300 dark:placeholder:text-ink-400">
                    @error('formTitle') <p class="text-brick-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2">
                    <select wire:model="formCategory"
                            class="bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-3 py-3 text-sm text-ink-900 dark:text-white focus:outline-none focus:border-vest-500">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model="formDate"
                           class="flex-1 bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-3 py-3 text-sm text-ink-900 dark:text-white focus:outline-none focus:border-vest-500">
                </div>
                @error('formCategory') <p class="text-brick-500 text-sm">{{ $message }}</p> @enderror
                @error('formDate') <p class="text-brick-500 text-sm">{{ $message }}</p> @enderror

                <textarea wire:model="formContent" placeholder="Isi catatan (opsional)" rows="4"
                          class="w-full bg-ink-50 dark:bg-ink-600 border border-ink-100 dark:border-ink-500 rounded-xl px-4 py-3 text-base text-ink-900 dark:text-white focus:outline-none focus:border-vest-500 placeholder:text-ink-300 dark:placeholder:text-ink-400"></textarea>

                <div>
                    <label class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-300 cursor-pointer">
                        <span class="text-lg">📎</span> Lampirkan file
                        <input type="file" wire:model="formAttachments" multiple class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    </label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ($formAttachments as $i => $upload)
                            <div class="flex items-center gap-1 text-xs font-mono bg-ink-100 dark:bg-ink-500 text-ink-700 dark:text-ink-200 px-2 py-1 rounded-lg">
                                📎 {{ $upload->getClientOriginalName() }}
                            </div>
                        @endforeach
                    </div>
                    @error('formAttachments.*') <p class="text-brick-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-ink-900 dark:bg-white text-white dark:text-ink-900 font-disp font-bold py-3.5 rounded-xl text-base">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</div>