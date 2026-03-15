<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::prefix('vote')
                ->middleware('web')
                ->group(base_path('routes/voting.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('voting.login'));

        $middleware->alias([
            'voting.admin' => \App\Http\Middleware\EnsureVotingAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!str_starts_with($request->getPathInfo(), '/vote')) {
                return null;
            }

            // Keep Laravel's default unauthenticated flow (redirect to login).
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            if (!$e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            $code = $e->getStatusCode();

            $messages = [
                403 => ['Akses Ditolak', $e->getMessage() ?: 'Kamu tidak punya akses ke halaman ini.'],
                404 => ['Tidak Ditemukan', 'Halaman yang kamu cari tidak ada.'],
                419 => ['Sesi Berakhir', 'Sesi sudah berakhir. Silakan ulangi aksi kamu.'],
                429 => ['Terlalu Banyak Permintaan', 'Coba lagi beberapa saat lagi.'],
            ];

            [$title, $message] = $messages[$code] ?? ['Error', $e->getMessage() ?: 'Terjadi kesalahan.'];

            return response()->view('voting.partials.error-page', [
                'code' => $code,
                'title' => $title,
                'message' => $message,
            ], $code);
        });
    })->create();
