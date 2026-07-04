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
        <div class="bg-white w-full rounded-t-3xl p-6">
            <h3 class="font-disp font-bold text-lg text-ink-900 mb-4">Task baru</h3>
            <input x-model="title" @keydown.enter="create" placeholder="Apa yang mau dikerjakan?" autofocus
                   class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3.5 text-base focus:outline-none focus:border-vest-500">

            <div class="flex gap-2 mt-4 overflow-x-auto no-scrollbar">
                @foreach ($projects as $project)
                    <button @click="projectId = {{ $project->id }}"
                            class="text-sm font-disp font-bold px-4 py-2 rounded-full whitespace-nowrap border"
                            :class="projectId === {{ $project->id }} ? 'text-white border-transparent' : 'bg-white text-ink-700 border-ink-100'"
                            :style="projectId === {{ $project->id }} && 'background: {{ $project->color }}'">
                        {{ $project->name }}
                    </button>
                @endforeach
            </div>

            <button @click="create" :disabled="saving" class="w-full bg-ink-900 text-white font-disp font-bold py-4 rounded-xl mt-5 text-base disabled:opacity-50">
                <span x-text="saving ? 'Menyimpan…' : 'Simpan ke Backlog'"></span>
            </button>
        </div>
    </div>
</div>
