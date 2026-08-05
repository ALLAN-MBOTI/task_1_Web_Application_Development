<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ============================================================================
 * AuthController
 * ============================================================================
 * Purpose: Manages user authentication workflow including login views, credential
 * validation against user email addresses, and session management.
 * ============================================================================
 */
class AuthController extends Controller
{
    /**
     * Display the login view form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication attempt.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validate form input using 'email' instead of 'username'
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Attempt authentication against the 'email' column
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            return redirect()->intended(route('invoice.index'));
        }

        // 3. Return back with an error on failure
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}