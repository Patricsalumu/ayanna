<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ServeuseSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $isServeuse = $user && strtolower((string) $user->role) === 'serveuse';

        if ($isServeuse && $request->hasSession()) {
            $lastActivity = $request->session()->get('serveuse_last_activity_at');
            $now = time();

            if ($lastActivity && ($now - $lastActivity) > 60) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->put('serveuse_last_activity_at', null);

                return redirect()->route('serveuse.login');
            }

            $request->session()->put('serveuse_last_activity_at', $now);
        }

        return $next($request);
    }
}
