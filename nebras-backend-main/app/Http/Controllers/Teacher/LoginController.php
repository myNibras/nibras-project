<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LoginController extends Controller
{
    /**
     * Show the teacher login form.
     */
    public function showLoginForm(): View
    {
        return view('teacher.auth.login');
    }

    /**
     * Handle an incoming authentication request for teachers.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('teacher')->attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'status' => true,
            ],
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('teacher.dashboard');
    }

    /**
     * Logout the authenticated teacher.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('teacher')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.login');
    }

    /**
     * Leave impersonation: log out from teacher and redirect admin back to panel.
     * Does not invalidate session so the admin (web guard) stays logged in.
     */
    public function leaveImpersonation(Request $request): RedirectResponse
    {
        $request->session()->forget('impersonate.admin_id');
        Auth::guard('teacher')->logout();

        $url = LaravelLocalization::localizeUrl(route('teachers.index'));

        return redirect($url);
    }
}
