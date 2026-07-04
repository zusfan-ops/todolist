@php
    $typeLabel = ['before' => 'SEBELUM', 'progress' => 'PROSES', 'after' => 'SESUDAH', 'proof' => 'BUKTI'];
    $typeClass = ['before' => 'bg-ink-700', 'progress' => 'bg-vest-600', 'after' => 'bg-leaf-500', 'proof' => 'bg-brick-500'];
@endphp

<div class="px-5 pt-6"
     x-data="{
        taskId: '',
        type: 'progress',
        capturing: false,
        async onFile(e) {
            const file = e.target.files[0];
            e.target.value = '';
            if (!file || !this.taskId) return;
            this.capturing = true;
            try {
                await window.KerjaKuPhoto.capturePhoto(file, { taskId: this.taskId, type: this.type });
            } finally {
                this.capturing = false;
            }
        }
     }">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-disp font-bold text-ink-900 text-base uppercase tracking-wider">Dokumentasi</h2>
        <label class="bg-ink-900 text-white text-sm font-disp font-bold px-4 py-2.5 rounded-lg cursor-pointer"
               :class="(!taskId || capturing) && 'opacity-50 pointer-events-none'">
            <span x-text="capturing ? 'Memproses…' : '📷 Ambil foto'"></span>
            <input type="file" accept="image/*" capture="environment" class="hidden" @change="onFile($event)">
        </label>
    </div>

    <div class="bg-white rounded-xl border border-ink-100 p-4 mb-5 space-y-3">
        <select x-model="taskId" class="w-full bg-ink-50 border border-ink-100 rounded-lg px-3 py-3 text-base">
            <option value="">Pilih task…</option>
            @foreach ($tasks as $task)
                <option value="{{ $task->id }}">{{ $task->project->name }} &middot; {{ $task->title }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            @foreach (['before', 'progress', 'after', 'proof'] as $t)
                <button type="button" @click="type = '{{ $t }}'"
                        class="flex-1 text-xs font-disp font-bold py-2.5 rounded-lg"
                        :class="type === '{{ $t }}' ? '{{ $typeClass[$t] }} text-white' : 'bg-ink-50 text-ink-500'">
                    {{ $typeLabel[$t] }}
                </button>
            @endforeach
        </div>
        <p class="text-sm text-ink-500" x-show="!taskId">Pilih task dulu sebelum jepret foto.</p>
    </div>

    <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar">
        <button wire:click="$set('filterType', null)" class="text-xs font-disp font-bold px-4 py-2 rounded-full border {{ ! $filterType ? 'bg-ink-900 text-white border-ink-900' : 'bg-white text-ink-700 border-ink-100' }}">Semua</button>
        @foreach ($typeLabel as $val => $label)
            <button wire:click="$set('filterType', '{{ $val }}')" class="text-xs font-disp font-bold px-4 py-2 rounded-full border {{ $filterType === $val ? 'bg-ink-900 text-white border-ink-900' : 'bg-white text-ink-700 border-ink-100' }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="grid grid-cols-3 gap-2">
        @foreach ($photos as $ph)
            <div class="aspect-square rounded-xl overflow-hidden relative bg-ink-100">
                <img src="{{ $ph->thumb_url }}" class="w-full h-full object-cover">
                <span class="absolute bottom-1 left-1 text-[10px] font-mono font-bold px-1.5 rounded text-white {{ $typeClass[$ph->type] }}">
                    {{ $typeLabel[$ph->type] }}
                </span>
            </div>
        @endforeach
    </div>
    @if ($photos->isEmpty())
        <p class="text-sm text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">Belum ada foto. Jepret dokumentasi pekerjaan pertamamu.</p>
    @endif
</div>
