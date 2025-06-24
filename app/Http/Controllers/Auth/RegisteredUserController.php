<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'country_code' => ['required', 'string'],
            'phone' => [
                'required',
                'string',
                'max:20',
                // Accepte 9 chiffres commençant par 0 ou non
                'regex:/^(0)?[0-9]{9}$/',
                'unique:users,phone'
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'entreprise_nom' => ['required', 'string', 'max:255'],
            'entreprise_logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'phone.unique' => __('validation.unique_phone'),
            'phone.regex' => __('validation.phone.regex'),
        ]);

        // Gestion du téléphone
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        if (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = substr($cleanPhone, 1);
        }
        $fullPhone = $request->country_code . $cleanPhone;
        if (User::where('phone', $fullPhone)->exists()) {
            return back()->withInput()->withErrors(['phone' => __('validation.unique_phone')]);
        }

        // Création de l'entreprise
        $entrepriseData = [
            'nom' => $request->entreprise_nom,
        ];
        if ($request->hasFile('entreprise_logo')) {
            $entrepriseData['logo'] = $request->file('entreprise_logo')->store('logos', 'public');
        }
        $entreprise = \App\Models\Entreprise::create($entrepriseData);

        // Création de l'utilisateur lié à l'entreprise
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $fullPhone,
            'password' => Hash::make($request->password),
            'entreprise_id' => $entreprise->id,
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('entreprises.show', $entreprise->id));
    }
}
