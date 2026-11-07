<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    /**
     * Execute authentication using username or email and regenerate session.
     * When $role is given, only accounts with that role can authenticate.
     */
    public function execute(string $login, string $password, bool $remember = false, ?string $role = null): bool
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [$field => $login, 'password' => $password];

        if ($role !== null) {
            $credentials['role'] = $role;
        }

        if (Auth::attempt($credentials, $remember)) {
            session()->regenerate();
            return true;
        }

        return false;
    }
}
