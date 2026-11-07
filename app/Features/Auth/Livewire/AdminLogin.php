<?php

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUserAction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Login Admin - StockApp')]
class AdminLogin extends Component
{
    public string $login = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(LoginUserAction $action): mixed
    {
        $this->validate();

        // Hanya akun ber-role admin yang dapat masuk lewat halaman ini.
        if ($action->execute($this->login, $this->password, $this->remember, 'admin')) {
            return $this->redirectRoute('admin.users', navigate: true);
        }

        $this->addError('login', 'Username/email atau password admin yang Anda masukkan tidak sesuai.');
        return null;
    }

    public function render(): View
    {
        return view('livewire.auth.admin-login');
    }
}
