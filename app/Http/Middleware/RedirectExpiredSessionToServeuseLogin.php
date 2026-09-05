<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RedirectExpiredSessionToServeuseLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (TokenMismatchException $e) {
            return $this->expiredResponse($request);
        } catch (HttpExceptionInterface $e) {
            if ((int) $e->getStatusCode() === 419) {
                return $this->expiredResponse($request);
            }

            throw $e;
        }

        if ((int) $response->getStatusCode() === 419) {
            return $this->expiredResponse($request);
        }

        return $response;
    }

    private function expiredResponse(Request $request): Response
    {
        $redirectUrl = url('/serveuse-login');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Session expirée. Veuillez vous reconnecter.',
                'redirect_url' => $redirectUrl,
            ], 419);
        }

        return redirect()->guest($redirectUrl)
            ->with('error', 'Session expirée. Veuillez vous reconnecter.');
    }
}
