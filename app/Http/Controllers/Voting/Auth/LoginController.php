<?php

namespace App\Http\Controllers\Voting\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voting\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return View::make('voting.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->role === 'admin') {
                return Redirect::route('voting.admin.events.index');
            }

            return Redirect::route('voting.landing');
        }

        return Redirect::back()->with('error', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('voting.login');
    }
}
