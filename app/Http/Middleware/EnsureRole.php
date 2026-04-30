<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Usage: 'role:admin'  or  'role:staff,reception'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        $user = auth()->user();

        if (! in_array($user->role, $roles, true)) {
            // Redirect to the correct panel for this user's role
            $target = match ($user->role) {
                'admin'     => '/admin',
                'staff'     => '/staff',
                'reception' => '/reception',
                default     => '/',
            };

            return redirect($target);
        }

        return $next($request);
    }
}
