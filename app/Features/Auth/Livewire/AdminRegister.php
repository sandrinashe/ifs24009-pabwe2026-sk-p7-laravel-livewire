<?php

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\RegisterUserAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Daftar Admin - StockApp')]
class AdminRegister extends Component
{
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $access_code = '';

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'access_code' => ['required', 'string'],
        ];
    }

    protected function configuredCode(): string
    {
        return (string) config('app.admin_registration_code', '');
    }

    public function register(RegisterUserAction $action): mixed
    {
        $configuredCode = $this->configuredCode();

        if ($configuredCode === '') {
            $this->addError('access_code', 'Pendaftaran admin sedang dinonaktifkan.');
            return null;
        }

        $this->validate();

        // Batasi percobaan tebak kode akses per alamat IP.
        $throttleKey = 'admin-register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('access_code', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            return null;
        }

        if (!hash_equals($configuredCode, $this->access_code)) {
            RateLimiter::hit($throttleKey, 60);
            $this->addError('access_code', 'Kode akses admin tidak valid.');
            return null;
        }

        RateLimiter::clear($throttleKey);

        $user = $action->execute([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'role' => 'admin',
        ]);

        Auth::login($user);
        session()->regenerate();
        session()->flash('success', 'Registrasi admin berhasil! Selamat datang di StockApp Pro.');

        return $this->redirectRoute('admin.users', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.admin-register', [
            'enabled' => $this->configuredCode() !== '',
        ]);
    }
}
