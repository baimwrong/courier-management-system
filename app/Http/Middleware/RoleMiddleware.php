<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact an administrator.',
            ]);
        }

        // Superadmin has universal administrative access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // If no specific roles required, any active authenticated staff user can proceed
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has one of the allowed roles
        if (!in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action for your role.'], 403);
            }

            abort(403, 'Unauthorized access: Your user role (' . $user->role_label . ') is not authorized to access this operational area.');
        }

        return $next($request);
    }
}
