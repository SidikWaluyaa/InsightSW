<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class LoginForm extends Form
{
    /** Jumlah percobaan gagal sebelum login dikunci sementara. */
    public const MAX_ATTEMPTS = 5;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    /** Sisa detik sampai boleh mencoba lagi (0 = tidak dikunci). Dipakai hitung mundur di halaman. */
    public int $retryAfter = 0;

    /** Sisa percobaan sebelum dikunci (null = belum ada percobaan gagal). */
    public ?int $attemptsLeft = null;

    protected function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid. Contoh: nama@shoeworkshop.id',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Kunci error: form.throttle (terlalu banyak percobaan), form.credentials (email/sandi salah),
     * form.inactive (akun dinonaktifkan Admin). Halaman login menampilkannya sebagai banner.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->retryAfter = 0;
        $this->attemptsLeft = null;

        $this->ensureIsNotRateLimited();

        // Kredensial diperiksa dulu tanpa membuat sesi login, supaya akun nonaktif tidak sempat "masuk"
        if (! Auth::validate($this->only(['email', 'password']))) {
            RateLimiter::hit($this->throttleKey());

            $this->attemptsLeft = RateLimiter::remaining($this->throttleKey(), self::MAX_ATTEMPTS);

            // Percobaan terakhir yang gagal langsung mengunci: tampilkan hitung mundur, bukan "sisa 0"
            $this->ensureIsNotRateLimited();

            throw ValidationException::withMessages([
                'form.credentials' => 'Email atau kata sandi salah.',
            ]);
        }

        $user = Auth::getLastAttempted();

        // Akun yang dinonaktifkan di menu Akun tidak boleh masuk walau kata sandinya benar
        if (! $user->status) {
            throw ValidationException::withMessages([
                'form.inactive' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin untuk mengaktifkannya kembali.',
            ]);
        }

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());

        Auth::user()->forceFill(['last_login_at' => now()])->saveQuietly();
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout(request()));

        $this->retryAfter = RateLimiter::availableIn($this->throttleKey());
        $this->attemptsLeft = 0;

        throw ValidationException::withMessages([
            'form.throttle' => 'Terlalu banyak percobaan masuk. Demi keamanan, login dikunci sementara.',
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
