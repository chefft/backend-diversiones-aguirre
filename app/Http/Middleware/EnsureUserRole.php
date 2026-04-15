<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login.client');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes acceso a la vista de cliente.');
        }

        return redirect()->route('client.dashboard')
            ->with('error', 'No tienes acceso a la vista administrativa.');
    }
}
