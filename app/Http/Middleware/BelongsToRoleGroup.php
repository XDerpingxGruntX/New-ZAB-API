<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class BelongsToRoleGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roleCategory): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Convert to lowercase for case-insensitive matching
        $roleCategory = strtolower($roleCategory);

        $isAuthorized = match ($roleCategory) {
            'manager' => $request->user()->isManager(),
            'senior' => $request->user()->isSenior(),
            'staff' => $request->user()->isStaff(),
            'instructor' => $request->user()->isInstructor(),
            default => throw new InvalidArgumentException("Unknown role category: {$roleCategory}")
        };

        if (! $isAuthorized) {
            abort(403);
        }

        return $next($request);
    }
}
