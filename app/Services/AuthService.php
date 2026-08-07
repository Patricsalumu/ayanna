<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function loginByFourDigits(string $username, string $password): bool
    {
        $user = \App\Models\User::where('name', $username)->first();

        if (!$user) {
            return false;
        }

        if (!preg_match('/^\d{4}$/', (string) $password)) {
            return false;
        }

        if (!Hash::check($password, $user->password)) {
            return false;
        }

        Auth::login($user, true);

        return true;
    }
}
