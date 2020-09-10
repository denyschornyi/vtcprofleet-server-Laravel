<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Setting;

class DemoModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param Closure                   $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if( Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', trans('admin.demomode'));
        }
        return $next($request);
    }
}
