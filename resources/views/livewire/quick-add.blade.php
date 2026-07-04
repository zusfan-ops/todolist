<div x-data="{
        open: false,
        title: '',
        projectId: {{ $defaultProjectId ?? 'null' }},
        saving: false,
        init() {
            window.addEventListener('open-quick-add', () => this.open = true);
        },
        async create() {
            if (!this.title.trim() || !this.projectId) return;
            this.saving = true;

            const result = await window.KerjaKuApi.sendJson('/api/tasks', 'POST', {
                project_id: this.projectId,
                title: this.title.trim(),
            });
            this.saving = false;

            // Queued (offline) counts as success from the user's point of view —
            // it'll sync later. A real HTTP failure (4xx/401/etc.) must not show
            // a false success toast.
            if (!result.queued && result.response && !result.response.ok) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal menyimpan task, coba lagi' } }));
                return;
            }

            this.title = '';
            this.open = false;
            window.dispatchEvent(new CustomEvent('task-updated'));
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Task masuk Backlog' } }));
        }
     }">
    <div x-show="open" x-cloak @click.self="open = false" class="absolute inset-0 bg-ink-900/50 flex items-end z-20">
        <div class="bg-white w-full rounded-t-3xl p-5">
            <h3 class="font-disp font-bold text-ink-900 mb-3">Task baru</h3>
            <input x-model="title" @keydown.enter="create" placeholder="Apa yang mau dikerjakan?" autofocus
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">

            <div class="flex gap-2 mt-3 overflow-x-auto no-scrollbar">
                @foreach ($projects as $project)
                    <button @click="projectId = {{ $project->id }}"
                            class="text-xs font-disp font-bold px-3 py-1.5 rounded-full whitespace-nowrap border"
                            :class="projectId === {{ $project->id }} ? 'text-white border-transparent' : 'bg-white text-ink-700 border-ink-100'"
                            :style="projectId === {{ $project->id }} && 'background: {{ $project->color }}'">
                        {{ $project->name }}
                    </button>
                @endforeach
            </div>

            <button @click="create" :disabled="saving" class="w-full bg-ink-900 text-white font-disp font-bold py-3.5 rounded-xl mt-4 disabled:opacity-50">
                <span x-text="saving ? 'Menyimpan…' : 'Simpan ke Backlog'"></span>
            </button>
        </div>
    </div>
</div>
