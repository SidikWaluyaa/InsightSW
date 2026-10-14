<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] #[Title('Masuk')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            // Kata sandi dikosongkan agar tidak dikirim ulang, lalu halaman diberi tahu untuk fokus / hitung mundur
            $this->form->reset('password');
            $this->dispatch('login-failed', retryAfter: $this->form->retryAfter);

            throw $e;
        }

        Session::regenerate();

        // Halaman awal mengikuti peran, supaya CS/Finance/Gudang tidak dilempar dari dashboard Marketing.
        // Tanpa wire:navigate: halaman dasbor memuat pustaka grafik lewat <script> di dalam halaman.
        $this->redirectIntended(default: route(Auth::user()->homeRoute(), absolute: false));
    }
}; ?>

@php
    $authError = $errors->first('form.throttle') ?: $errors->first('form.inactive') ?: $errors->first('form.credentials');
    $isThrottled = $errors->has('form.throttle');

    // Reset kata sandi lewat email hanya ditawarkan jika email memang bisa terkirim
    $canResetByEmail = Route::has('password.request') && ! in_array(config('mail.default'), ['log', 'array'], true);

    $inputBase = 'block w-full h-12 rounded-2xl border bg-slate-50 dark:bg-gray-800/40 text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-gray-500 transition-all focus:outline-none focus:ring-4 focus:bg-white dark:focus:bg-gray-800/70';
    $inputOk = 'border-slate-200 dark:border-gray-700 focus:border-emerald-500 focus:ring-emerald-500/15';
    $inputBad = 'border-rose-400 dark:border-rose-500/70 focus:border-rose-500 focus:ring-rose-500/15';
@endphp

<div id="login-form"
    x-data="{
        showPassword: false,
        capsLock: false,
        lockLeft: {{ (int) $form->retryAfter }},
        netError: null,
        timer: null,
        init() { this.startCountdown(); },
        startCountdown() {
            clearInterval(this.timer);
            if (this.lockLeft <= 0) return;
            this.timer = setInterval(() => { if (--this.lockLeft <= 0) clearInterval(this.timer); }, 1000);
        },
        get lockLabel() {
            const m = Math.floor(this.lockLeft / 60), s = this.lockLeft % 60;
            return m > 0 ? m + ' menit ' + s + ' detik' : s + ' detik';
        },
        onFailed(retryAfter) {
            this.lockLeft = retryAfter || 0;
            this.startCountdown();
            this.$nextTick(() => (this.lockLeft > 0 ? this.$refs.email : this.$refs.password).focus());
        },
    }"
    x-on:login-failed.window="onFailed($event.detail.retryAfter)"
    x-on:login-net-error.window="netError = $event.detail.kind"
    x-on:offline.window="netError = 'offline'"
    x-on:online.window="if (netError === 'offline') netError = null">

    {{-- Judul --}}
    <div class="mb-7">
        <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Selamat datang kembali</h1>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-gray-400">Masuk dengan akun kerja Anda untuk melanjutkan.</p>
    </div>

    {{-- Pesan sukses (mis. setelah reset kata sandi) --}}
    @if (session('status'))
        <div role="status" class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3">
            <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('status') }}</p>
        </div>
    @endif

    {{-- Gagal masuk: kredensial salah / akun nonaktif / terkunci sementara --}}
    @if ($authError)
        <div role="alert" wire:key="auth-error-{{ md5($authError . $form->retryAfter . $form->attemptsLeft) }}"
            class="mb-5 flex items-start gap-3 rounded-2xl border px-4 py-3 {{ $isThrottled ? 'border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/30' : 'border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/30' }}">
            @if ($isThrottled)
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            @else
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            @endif
            <div class="text-sm {{ $isThrottled ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                <p class="font-bold">{{ $authError }}</p>
                @if ($isThrottled)
                    <p class="mt-0.5" x-show="lockLeft > 0">Coba lagi dalam <span class="font-bold tabular-nums" x-text="lockLabel">{{ $form->retryAfter }} detik</span>.</p>
                    <p class="mt-0.5" x-show="lockLeft <= 0" x-cloak>Anda sudah bisa mencoba masuk lagi.</p>
                @elseif ($errors->has('form.credentials'))
                    <p class="mt-0.5">
                        Periksa kembali penulisan email dan kata sandi Anda.
                        @if (! is_null($form->attemptsLeft) && $form->attemptsLeft <= 2)
                            <span class="font-semibold">Sisa {{ $form->attemptsLeft }} percobaan sebelum login dikunci sementara.</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Gangguan koneksi / server (tidak ada balasan validasi dari server) --}}
    <div role="alert" x-show="netError" x-cloak x-transition.opacity
        class="mb-5 flex items-start gap-3 rounded-2xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 px-4 py-3">
        <svg class="w-5 h-5 mt-0.5 shrink-0 text-slate-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m-2.829-2.829a5 5 0 000-7.07m-4.243 3.535h.01M3 3l18 18" /></svg>
        <div class="text-sm text-slate-700 dark:text-gray-300">
            <p class="font-bold" x-text="{
                offline: 'Tidak ada koneksi internet.',
                expired: 'Sesi halaman ini sudah kedaluwarsa.',
                server: 'Server sedang bermasalah.',
            }[netError] || 'Tidak dapat terhubung ke server.'"></p>
            <p class="mt-0.5" x-show="netError === 'expired'">
                <button type="button" class="font-semibold text-emerald-600 dark:text-emerald-400 underline underline-offset-2" x-on:click="window.location.reload()">Muat ulang halaman</button> lalu coba masuk lagi.
            </p>
            <p class="mt-0.5" x-show="netError === 'offline'">Periksa jaringan Anda, lalu coba lagi.</p>
            <p class="mt-0.5" x-show="netError !== 'expired' && netError !== 'offline'">Coba lagi beberapa saat. Jika masih gagal, hubungi Admin.</p>
        </div>
    </div>

    <form wire:submit="login" novalidate class="space-y-5"
        x-on:submit.capture="if (!navigator.onLine) { $event.preventDefault(); $event.stopImmediatePropagation(); netError = 'offline'; } else if (netError !== 'expired') { netError = null; }">

        {{-- Email --}}
        <div>
            <label for="email" class="block mb-1.5 text-xs font-bold text-slate-600 dark:text-gray-300">Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </span>
                <input wire:model="form.email" x-ref="email" id="email" name="email" type="email" inputmode="email"
                    autocomplete="username" autocapitalize="none" spellcheck="false" autofocus
                    placeholder="nama@shoeworkshop.id"
                    @error('form.email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="{{ $inputBase }} pl-12 pr-4 {{ $errors->has('form.email') ? $inputBad : $inputOk }}">
            </div>
            @error('form.email')
                <p id="email-error" class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kata sandi --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-600 dark:text-gray-300">Kata sandi</label>
                @if ($canResetByEmail)
                    <a href="{{ route('password.request') }}" wire:navigate
                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline underline-offset-2 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </span>
                <input wire:model="form.password" x-ref="password" id="password" name="password"
                    :type="showPassword ? 'text' : 'password'" type="password"
                    autocomplete="current-password" placeholder="Masukkan kata sandi"
                    x-on:keyup="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                    x-on:blur="capsLock = false"
                    @error('form.password') aria-invalid="true" aria-describedby="password-error" @enderror
                    class="{{ $inputBase }} pl-12 pr-12 {{ $errors->has('form.password') ? $inputBad : $inputOk }}">
                <button type="button" x-on:click="showPassword = !showPassword; $refs.password.focus()"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 rounded-r-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" :aria-pressed="showPassword.toString()">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                </button>
            </div>
            @error('form.password')
                <p id="password-error" class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
            <p x-show="capsLock" x-cloak class="mt-1.5 flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Caps Lock sedang aktif.
            </p>
        </div>

        {{-- Ingat saya --}}
        <label for="remember" class="flex items-center gap-2.5 cursor-pointer select-none">
            <input wire:model="form.remember" id="remember" name="remember" type="checkbox"
                class="w-4 h-4 rounded border-slate-300 dark:border-gray-600 dark:bg-gray-800 text-emerald-500 focus:ring-emerald-500/30 focus:ring-offset-0">
            <span class="text-sm text-slate-600 dark:text-gray-300">Ingat saya di perangkat ini</span>
        </label>

        {{-- Tombol masuk --}}
        <button type="submit" :disabled="lockLeft > 0" wire:loading.attr="disabled" wire:target="login"
            class="w-full h-12 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/25 transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/30 disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100">
            <svg wire:loading wire:target="login" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span wire:loading.remove wire:target="login">
                <span x-show="lockLeft <= 0">Masuk</span>
                <span x-show="lockLeft > 0" x-cloak>Tunggu <span class="tabular-nums" x-text="lockLabel"></span></span>
            </span>
            <span wire:loading wire:target="login">Memeriksa…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-500 dark:text-gray-400">
        @if ($canResetByEmail)
            Belum punya akun? Hubungi Admin untuk dibuatkan.
        @else
            Lupa kata sandi atau belum punya akun? Hubungi Admin.
        @endif
    </p>
</div>

@script
<script>
    // Kegagalan request (server error, sesi kedaluwarsa, jaringan putus) ditampilkan sebagai pesan di form,
    // bukan modal error bawaan Livewire. Hanya berlaku selama form login masih ada di halaman.
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (!document.getElementById('login-form')) return;

            preventDefault();

            const kind = status === 419 ? 'expired' : (status >= 500 ? 'server' : 'network');
            window.dispatchEvent(new CustomEvent('login-net-error', { detail: { kind } }));
        });
    });
</script>
@endscript
