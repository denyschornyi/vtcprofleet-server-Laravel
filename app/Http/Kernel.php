<?php

namespace App\Http;

use App\Http\Middleware\AccountLanguageMiddleware;
use App\Http\Middleware\AdminLanguageMiddleware;
use App\Http\Middleware\DemoModeMiddleware;
use App\Http\Middleware\DispatcherLanguageMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\FleetLanguageMiddleware;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\ProviderApiMiddleware;
use App\Http\Middleware\ProviderLanguageMiddleware;
use App\Http\Middleware\RedirectIfAccount;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfDispatcher;
use App\Http\Middleware\RedirectIfFleet;
use App\Http\Middleware\RedirectIfNotAccount;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotDispatcher;
use App\Http\Middleware\RedirectIfNotFleet;
use App\Http\Middleware\RedirectIfNotProvider;
use App\Http\Middleware\RedirectIfProvider;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,


        ],

        'api' => [
            //'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'account' => RedirectIfNotAccount::class,
        'account.guest' => RedirectIfAccount::class,
        'fleet' => RedirectIfNotFleet::class,
        'fleet.guest' => RedirectIfFleet::class,
        'dispatcher' => RedirectIfNotDispatcher::class,
        'dispatcher.guest' => RedirectIfDispatcher::class,
        'provider' => RedirectIfNotProvider::class,
        'provider.guest' => RedirectIfProvider::class,
        'provider.api' => ProviderApiMiddleware::class,
        'admin' => RedirectIfNotAdmin::class,
        'admin.guest' => RedirectIfAdmin::class,
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'language' => LanguageMiddleware::class,
        'provider.language' => ProviderLanguageMiddleware::class,
        'admin.language' => AdminLanguageMiddleware::class,
        'fleet.language' => FleetLanguageMiddleware::class,
        'account.language' => AccountLanguageMiddleware::class,
        'dispatcher.language' => DispatcherLanguageMiddleware::class,
        'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
        'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
        'role' => RoleMiddleware::class,
        'permission' => PermissionMiddleware::class,
        'demo' => DemoModeMiddleware::class
    ];
}
