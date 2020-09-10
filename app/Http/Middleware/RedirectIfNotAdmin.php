<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param Closure                   $next
	 * @param  string|null              $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admin')
	{
	    if (!Auth::guard($guard)->check()) {
	        return redirect('admin/login');
	    }

	    return $next($request);
	}
}
