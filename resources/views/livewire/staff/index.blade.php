<div class="px-5 pt-6 pb-5 space-y-6" x-data="{ copied: false, copy(link) { navigator.clipboard.writeText(link); this.copied = true; setTimeout(() => this.copied = false, 1500) } }">
    <div class="flex items-center justify-between">
        <h2 class="font-disp font-bold text-ink-900 text-base uppercase tracking-wider">Kelola Staf</h2>
        <a href="{{ route('kanban') }}" wire:navigate class="text-xs text-ink-500 font-disp font-bold">&larr; Kembali</a>
    </div>

    <div class="bg-white rounded-xl border border-ink-100 p-5">
        <h3 class="font-disp font-bold text-sm uppercase tracking-wider text-ink-700 mb-4">Undang Staf Baru</h3>
        <form wire:submit="invite" class="space-y-3">
            <div>
                <input wire:model="inviteName" placeholder="Nama staf" class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3.5 text-base focus:outline-none focus:border-vest-500">
                @error('inviteName') <p class="text-brick-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <input wire:model="inviteEmail" type="email" placeholder="Email staf" class="w-full bg-ink-50 border border-ink-100 rounded-xl px-4 py-3.5 text-base focus:outline-none focus:border-vest-500">
                @error('inviteEmail') <p class="text-brick-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-vest-500 text-ink-900 font-disp font-bold py-3.5 rounded-xl text-base">
                <span wire:loading.remove wire:target="invite">Buat Link Undangan</span>
                <span wire:loading wire:target="invite">Memproses…</span>
            </button>
        </form>

        @if ($generatedLink)
            <div class="mt-4 bg-leaf-500/10 border border-leaf-500/30 rounded-xl p-4">
                <p class="text-xs text-ink-700 mb-2">Kirim link ini ke staf lewat WhatsApp/chat:</p>
                <div class="flex items-center gap-2">
                    <input readonly value="{{ $generatedLink }}" class="flex-1 bg-white border border-ink-100 rounded-lg px-3 py-2.5 text-xs font-mono truncate">
                    <button type="button" @click="copy('{{ $generatedLink }}')" class="shrink-0 bg-ink-900 text-white text-xs font-disp font-bold px-4 py-2.5 rounded-lg">
                        <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                    </button>
                </div>
                <p class="text-xs text-ink-500 mt-2">Berlaku 7 hari.</p>
            </div>
        @endif
    </div>

    @if ($invitations->count())
        <div>
            <h3 class="font-disp font-bold text-sm uppercase tracking-wider text-ink-700 mb-3">Undangan Menunggu</h3>
            <div class="space-y-2.5">
                @foreach ($invitations as $invitation)
                    <div wire:key="inv-{{ $invitation->id }}" class="bg-white rounded-xl border border-ink-100 px-4 py-3.5 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-base font-bold text-ink-900 truncate">{{ $invitation->name }}</p>
                            <p class="text-xs text-ink-500 truncate">{{ $invitation->email }}</p>
                        </div>
                        <button wire:click="revokeInvitation({{ $invitation->id }})"
                                wire:confirm="Batalkan undangan ini?"
                                class="shrink-0 text-xs font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-3 py-1.5">Batalkan</button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h3 class="font-disp font-bold text-sm uppercase tracking-wider text-ink-700 mb-3">Staf Aktif</h3>
        @if ($staffMembers->isEmpty())
            <p class="text-sm text-ink-500 bg-white border border-dashed border-ink-300 rounded-xl p-6 text-center">
                Belum ada staf. Undang lewat form di atas.
            </p>
        @else
            <div class="space-y-2.5">
                @foreach ($staffMembers as $staff)
                    <div wire:key="staff-{{ $staff->id }}" class="bg-white rounded-xl border border-ink-100 p-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="min-w-0">
                                <p class="text-base font-bold text-ink-900 truncate">{{ $staff->name }}</p>
                                <p class="text-xs text-ink-500 truncate">{{ $staff->email }}</p>
                            </div>
                            <button wire:click="removeStaff({{ $staff->id }})"
                                    wire:confirm="Hapus staf ini? Akunnya tidak bisa lagi login."
                                    class="shrink-0 text-xs font-disp font-bold text-brick-500 border border-brick-500 rounded-lg px-3 py-1.5">Hapus</button>
                        </div>

                        <p class="text-xs font-disp font-bold uppercase tracking-wider text-ink-500 mb-2">Proyek yang ditugaskan</p>
                        @if ($projects->isEmpty())
                            <p class="text-xs text-ink-300">Belum ada proyek untuk ditugaskan.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($projects as $project)
                                    @php $assigned = $staff->assignedProjects->contains('id', $project->id); @endphp
                                    <button wire:click="toggleAssignment({{ $staff->id }}, {{ $project->id }})"
                                            class="text-xs font-disp font-bold px-3 py-1.5 rounded-full border {{ $assigned ? 'bg-leaf-500/15 text-leaf-600 border-leaf-500/40' : 'bg-ink-50 text-ink-500 border-ink-100' }}">
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
