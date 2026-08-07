<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'serveuse_login' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->resolveLoginIdentifier();
        $password = (string) $this->input('password');
        $serveuseLogin = $this->boolean('serveuse_login');

        $requiresLoginIdentifier = !($serveuseLogin && $this->isValidPin($password));
        if ($requiresLoginIdentifier && ($login === null || $login === '')) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        $user = null;
        if ($serveuseLogin && $this->isValidPin($password)) {
            $user = User::query()
                ->where('code_pin', $password)
                ->where('role', 'serveuse')
                ->first();
        } else {
            $user = User::query()
                ->where(function ($query) use ($login): void {
                    $query->where('name', $login)
                        ->orWhere('email', $login)
                        ->orWhere('phone', $login);
                })
                ->when($serveuseLogin, function ($query): void {
                    $query->where('role', 'serveuse');
                })
                ->first();
        }

        if ($user && $this->isValidPin($password) && $this->matchesPin($user, $password)) {
            Auth::login($user, $this->boolean('remember'));
        } elseif ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } elseif ($user && Auth::attempt(['name' => $user->name, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } elseif ($user && Auth::attempt(['phone' => $user->phone, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } elseif (Auth::attempt(['email' => $login, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } elseif (Auth::attempt(['name' => $login, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } elseif (Auth::attempt(['phone' => $login, 'password' => $password], $this->boolean('remember'))) {
            // ok
        } else {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }

    protected function resolveLoginIdentifier(): ?string
    {
        $login = $this->input('username') ?? $this->input('email') ?? $this->input('login');

        return is_string($login) ? trim($login) : null;
    }

    protected function isValidPin(?string $password): bool
    {
        return is_string($password) && preg_match('/^\d{4}$/', $password) === 1;
    }

    protected function matchesPin(User $user, ?string $password): bool
    {
        if (!$this->isValidPin($password)) {
            return false;
        }

        if (!empty($user->code_pin) && (string) $user->code_pin === (string) $password) {
            return true;
        }

        return Hash::check($password, $user->password);
    }
}
