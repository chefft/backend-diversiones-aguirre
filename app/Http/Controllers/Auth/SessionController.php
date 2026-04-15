<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function showAdminLogin(): RedirectResponse|View
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login-admin');
    }

    public function showClientLogin(): RedirectResponse|View
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login-client');
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        $credentials = $this->validatedCredentials($request);
        $remember = $request->boolean('remember');

        if (!Auth::attempt([...$credentials, 'role' => 'admin'], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Credenciales administrativas invalidas.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function loginClient(Request $request): RedirectResponse
    {
        $credentials = $this->validatedCredentials($request);
        $remember = $request->boolean('remember');

        if (!Auth::attempt([...$credentials, 'role' => 'client'], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Credenciales de cliente invalidas.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('client.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.client')
            ->with('status', 'Sesion cerrada correctamente.');
    }

    private function validatedCredentials(Request $request): array
    {
        return $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
    }

    private function redirectByRole(): RedirectResponse
    {
        if (Auth::user()?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('client.dashboard');
    }
}
