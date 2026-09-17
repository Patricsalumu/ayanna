<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RejectBlockedEntreprise
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = strtolower(trim((string) ($user?->role ?? '')));

        if ($user && $user->entreprise_id && $role !== 'super_admin' && $user->entreprise?->blocked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Votre entreprise a été bloquée. Vous ne pourrez pas continuer à utiliser l’application Ayanna Web. Veuillez contacter l’équipe Ayanna ERP pour en savoir plus. Contact : +243997554905.\n\nAttention : Toute utilisation d’un logiciel sans autorisation de l’éditeur ou toute autre forme de piratage expose votre établissement à des poursuites judiciaires.\n\nYour company has been blocked. You cannot continue using Ayanna Web. Please contact the Ayanna ERP team for more information. Contact: +243997554905.\n\nWarning: Using software without the publisher’s authorization or any form of piracy may expose your establishment to legal proceedings.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $message,
                    'redirect_url' => route('login'),
                ], 403);
            }

            return redirect()->route('login')->with('blocked_error', $message);
        }

        return $next($request);
    }
}
