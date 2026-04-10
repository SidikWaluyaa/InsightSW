<div>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-black text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                    Manajemen Pengguna
                </h2>
                <p class="text-xs font-bold text-slate-400 dark:text-gray-500 mt-1 uppercase tracking-widest text-emerald-500">Akses Tim & Kontrol Akun</p>
            </div>
            <button onclick="Livewire.dispatch('create-user')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-6 rounded-2xl transition-all shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 text-sm active:scale-95 group">
                <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center group-hover:rotate-90 transition-transform duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </div>
                Tambah Kru Baru
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Search and Filter --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="relative w-full lg:max-w-sm">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kru (nama/email)..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800/50 border border-transparent focus:border-emerald-500 rounded-xl text-sm focus:ring-0 dark:text-gray-200 transition-all">
            </div>
            
            <div class="flex items-center gap-4 w-full lg:w-auto">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">Role:</label>
                    <select wire:model.live="filterRole" class="bg-gray-50 dark:bg-gray-800/50 border-none rounded-xl text-xs font-bold text-slate-600 dark:text-gray-300 focus:ring-emerald-500">
                        <option value="">Semua</option>
                        <option value="Admin">Admin</option>
                        <option value="Editor">Editor</option>
                        <option value="Viewer">Viewer</option>
                        <option value="CS">CS (Customer Service)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest shrink-0">Status:</label>
                    <select wire:model.live="filterStatus" class="bg-gray-50 dark:bg-gray-800/50 border-none rounded-xl text-xs font-bold text-slate-600 dark:text-gray-300 focus:ring-emerald-500">
                        <option value="">Semua</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non-Aktif</option>
                    </select>
                </div>
                <div wire:loading class="text-emerald-500">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30 text-[10px] uppercase font-black text-slate-400 dark:text-gray-500 tracking-[0.2em]">
                            <th class="px-8 py-5">Info Profil</th>
                            <th class="px-6 py-5">Peran (Role)</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-6 py-5 text-center">Login Terakhir</th>
                            <th class="px-8 py-5 text-right">Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-all duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="relative shrink-0">
                                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $user->status ? 'from-emerald-400 to-teal-500' : 'from-slate-400 to-slate-500' }} flex items-center justify-center text-white font-black text-lg shadow-lg shadow-emerald-500/10">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900 {{ $user->status ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-white leading-tight">{{ $user->name }}</span>
                                            <span class="text-xs font-bold text-slate-400 dark:text-gray-500 mt-0.5">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $roleStyles = [
                                            'Admin' => 'bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
                                            'Editor' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                                            'Viewer' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400',
                                            'CS' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $roleStyles[$user->role] ?? $roleStyles['Viewer'] }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <button wire:click="toggleStatus({{ $user->id }})" 
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border {{ $user->status ? 'border-emerald-500/20 text-emerald-500 hover:bg-emerald-50' : 'border-rose-500/20 text-rose-500 hover:bg-rose-50' }} transition-colors text-[10px] font-black uppercase tracking-widest">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $user->status ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        {{ $user->status ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-gray-300">{{ $user->last_login_at ? $user->last_login_at->format('d M Y') : '-' }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $user->last_login_at ? $user->last_login_at->format('H:i') : '' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="edit({{ $user->id }})" class="p-2 text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-500/10 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        @if($user->id !== Auth::id())
                                            <button onclick="confirm('Yakin ingin menghapus kru ini?') || event.stopImmediatePropagation()" 
                                                wire:click="delete({{ $user->id }})" class="p-2 text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-500/10 rounded-xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-20 h-20 rounded-full bg-slate-50 dark:bg-gray-800 flex items-center justify-center text-slate-300 dark:text-gray-700">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-400 dark:text-gray-500">Tidak ada penguna ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-8 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Complex --}}
    @if($isOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeModal"></div>
            
            <div class="relative bg-white dark:bg-gray-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl border border-white/20 overflow-hidden" 
                 x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
                
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex flex-col">
                        <h3 class="font-black text-xl text-slate-800 dark:text-white">{{ $isEdit ? 'Profil Kru' : 'Kru Baru' }}</h3>
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mt-1">Lengkapi informasi di bawah ini</p>
                    </div>
                    <button wire:click="closeModal" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Nama Lengkap</label>
                                <input wire:model="name" type="text" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                                @error('name') <span class="text-[10px] font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-span-1 md:col-span-2">
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Email Perusahaan</label>
                                <input wire:model="email" type="email" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                                @error('email') <span class="text-[10px] font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Role Akses</label>
                                <select wire:model="role" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                                    <option value="Viewer">Viewer</option>
                                    <option value="Editor">Editor</option>
                                    <option value="Admin">Admin</option>
                                    <option value="CS">CS (Customer Service)</option>
                                </select>
                                @error('role') <span class="text-[10px] font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Status Akun</label>
                                <select wire:model="status" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 dark:bg-gray-800 my-2"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                                    {{ $isEdit ? '*Kosongkan password jika tidak ingin mengganti' : 'Tentukan password minimal 6 karakter' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Password</label>
                                <input wire:model="password" type="password" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                                @error('password') <span class="text-[10px] font-bold text-rose-500 mt-2 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-2 block">Konfirmasi</label>
                                <input wire:model="password_confirmation" type="password" class="w-full bg-gray-50 dark:bg-gray-800/50 border-transparent focus:border-emerald-500 rounded-2xl p-4 text-sm font-bold text-slate-700 dark:text-gray-200 transition-all focus:ring-0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-gray-50 dark:bg-gray-800/50 flex flex-col md:flex-row gap-4">
                    <button wire:click="closeModal" class="flex-1 px-8 py-4 text-sm font-black text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">Batal</button>
                    <button wire:click="{{ $isEdit ? 'update' : 'store' }}" class="flex-1 px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-black rounded-2xl transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                        {{ $isEdit ? 'Perbarui Profil' : 'Daftarkan Kru' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
