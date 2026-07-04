<div class="px-5 pt-5 pb-5 space-y-5" x-data="{ copied: false, copy(link) { navigator.clipboard.writeText(link); this.copied = true; setTimeout(() => this.copied = false, 1500) } }">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 text-sm uppercase tracking-wider">Kelola Staf</h2>
        <a href="{{ route('kanban') }}" wire:navigate class="text-[11px] text-ink-500 font-disp font-bold">&larr; Kembali</a>
    </div>

    <div class="bg-white rounded-xl border border-ink-100 p-4">
        <h3 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-700 mb-3">Undang Staf Baru</h3>
        <form wire:submit="invite" class="space-y-2">
            <div>
                <input wire:model="inviteName" placeholder="Nama staf" class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
                @error('inviteName') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <input wire:model="inviteEmail" type="email" placeholder="Email staf" class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-vest-500">
                @error('inviteEmail') <p class="text-brick-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-vest-500 text-ink-900 font-disp font-bold py-3 rounded-xl">
                <span wire:loading.remove wire:target="invite">Buat Link Undangan</span>
                <span wire:loading wire:target="invite">Memproses…</span>
            </button>
        </form>

        @if ($generatedLink)
            <div class="mt-3 bg-leaf-500/10 border border-leaf-500/30 rounded-xl p-3">
                <p class="text-[11px] text-ink-700 mb-1.5">Kirim link ini ke staf lewat WhatsApp/chat:</p>
                <div class="flex items-center gap-2">
                    <input readonly value="{{ $generatedLink }}" class="flex-1 bg-white border border-ink-100 rounded-lg px-2 py-2 text-[11px] font-mono truncate">
                    <button type="button" @click="copy('{{ $generatedLink }}')" class="shrink-0 bg-ink-900 text-white text-[10px] font-disp font-bold px-3 py-2 rounded-lg">
                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
                <p class="text-[10px] text-ink-500 mt-1.5">Berlaku 7 hari.</p>
            </div>
        @endif
    </div>

    @if ($invitations->count())
        <div>
            <h3 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-700 mb-2">Undangan Menunggu</h3>
            <div class="space-y-2">
                @foreach ($invitations as $invitation)
                    <div wire:key="inv-{{ $invitation->id }}" class="bg-white rounded-xl border border-ink-100 px-4 py-3 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-ink-900 truncate">{{ $invitation->name }}</p>
                            <p class="text-[11px] text-ink-500 truncate">{{ $invitation->email }}</p>
                        </div>
                        <button wire:click="revokeInvitation({{ $invitation->id }})"
                                wire:confirm="Batalkan undangan ini?"
                                class="shrink-0 text-[10px] font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-2 py-1">Batalkan</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h3 class="font-disp font-bold text-xs uppercase tracking-wider text-ink-700 mb-2">Staf Aktif</h3>
        @if ($staffMembers->isEmpty())
            <p class="text-xs text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">
                Belum ada staf. Undang lewat form di atas.
            </p>
        @else
            <div class="space-y-2">
                @foreach ($staffMembers as $staff)
                    <div wire:key="staff-{{ $staff->id }}" class="bg-white rounded-xl border border-ink-100 p-4">
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-ink-900 truncate">{{ $staff->name }}</p>
                                <p class="text-[11px] text-ink-500 truncate">{{ $staff->email }}</p>
                            </div>
                            <button wire:click="removeStaff({{ $staff->id }})"
                                    wire:confirm="Hapus staf ini? Akunnya tidak bisa lagi login."
                                    class="shrink-0 text-[10px] font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-2 py-1">Hapus</button>
                        </div>

                        <p class="text-[10px] font-disp font-bold uppercase tracking-wider text-ink-500 mb-1.5">Proyek yang ditugaskan</p>
                        @if ($projects->isEmpty())
                            <p class="text-[11px] text-ink-300">Belum ada proyek untuk ditugaskan.</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($projects as $project)
                                    @php $assigned = $staff->assignedProjects->contains('id', $project->id); @endphp
                                    <button wire:click="toggleAssignment({{ $staff->id }}, {{ $project->id }})"
                                            class="text-[11px] font-disp font-bold px-2.5 py-1 rounded-full border {{ $assigned ? 'bg-leaf-500/15 text-leaf-600 border-leaf-500/40' : 'bg-ink-50 text-ink-500 border-ink-100' }}">
                                        {{ $assigned ? '✓ ' : '' }}{{ $project->name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
