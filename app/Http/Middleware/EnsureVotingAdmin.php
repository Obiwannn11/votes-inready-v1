<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVotingAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized.');
            }
            return \Illuminate\Support\Facades\Redirect::route('voting.login')
                ->with('error', 'Akses khusus admin.');
        }

        return $next($request);
    }
}
