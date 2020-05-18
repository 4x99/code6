<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param  Closure  $next
     * @param  null  $guard
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|mixed|object
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
            } else {
                return redirect()->guest('login');
            }
        }
        return $next($request);
    }
}
