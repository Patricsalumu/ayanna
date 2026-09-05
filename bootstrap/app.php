<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\RedirectExpiredSessionToServeuseLogin::class);

        $middleware->alias([
            'serveuse.session.timeout' => \App\Http\Middleware\ServeuseSessionTimeout::class,
            'role.access' => \App\Http\Middleware\EnsureRoleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
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
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ((int) $e->getStatusCode() !== 419) {
                return null;
            }

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
        });
    })->create();
