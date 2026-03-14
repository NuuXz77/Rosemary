<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Izinkan akses jika ada sesi PIN student ATAU user terautentikasi dengan permission sales.view
        if (! session()->has('pos_student_id') && ! (Auth::check() && Auth::user()->can('sales.view'))) {
            return redirect()->route('pos.login')
                ->with('error', 'Silakan login dengan PIN terlebih dahulu.');
        }

        return $next($request);
    }
}
