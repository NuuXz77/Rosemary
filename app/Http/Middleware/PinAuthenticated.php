<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require student PIN authentication.
 *
 * Students authenticate via their 4-digit PIN (stored in `students.pin`).
 * Upon successful verification the student's ID is stored in the session as
 * `pos_student_id`.  This middleware checks that key; if absent it redirects
 * the request to the PIN login page.
 */
class PinAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('pos_student_id')) {
            return redirect()->route('pos.login')
                ->with('error', 'Silakan login dengan PIN terlebih dahulu.');
        }

        return $next($request);
    }
}
