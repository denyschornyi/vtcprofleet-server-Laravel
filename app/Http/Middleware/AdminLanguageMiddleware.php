<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Config;
use Illuminate\Http\Request;

class AdminLanguageMiddleware
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
        Config::set('auth.admins.users.model', 'App\Admin');

       if ( Auth::check()) {

            $language = "en";
            if( Auth::user()->language){
               $language = Auth::user()->language;
           }

           App::setLocale($language);

       }
       return $next($request);

    }
}
