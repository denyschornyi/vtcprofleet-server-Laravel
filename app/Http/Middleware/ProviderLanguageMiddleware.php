<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Config;
use Illuminate\Http\Request;

class ProviderLanguageMiddleware
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
        Config::set('auth.providers.users.model', 'App\Provider');

       if ( Auth::check()) {
            $language = "en";
            if( Auth::user()->profile){
               $language = Auth::user()->profile->language;
           }

           App::setLocale($language);

       }
       return $next($request);

    }
}
