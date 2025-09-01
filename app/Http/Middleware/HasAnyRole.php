<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HasAnyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $requestedRoles = collect($roles)->map(function ($role) {
            return Role::tryFrom(Str::upper($role));
        })->filter();

        if ($requestedRoles->isEmpty() || ! $request->user()->roles->contains(
            fn (Role $userRole) => $requestedRoles->contains($userRole)
        )) {
            abort(403);
        }

        return $next($request);
    }
}
