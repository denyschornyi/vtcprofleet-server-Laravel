<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAccount
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param Closure                   $next
	 * @param  string|null              $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'account')
	{
	    if (!Auth::guard($guard)->check()) {
	        return redirect('account/login');
	    }

	    return $next($request);
	}
}
