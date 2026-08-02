<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'ckfinder/*',
            // payOS gọi webhook server-to-server nên không có CSRF token;
            // tính toàn vẹn được đảm bảo bằng chữ ký HMAC trong payload.
            'payos/webhook',
        ]);
        
        $middleware->encryptCookies(except: [
            'ckCsrfToken',
        ]);
        
        $middleware->redirectGuestsTo(fn (Request $request) => $request->expectsJson() ? null : route('admin_login'));
        $middleware->redirectUsersTo(\App\Providers\RouteServiceProvider::HOME);



        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin\Admin::class,
            'ckfinderAuth' => \App\Http\Middleware\ckFinderAuthentication::class,
            'auth:api' => \Illuminate\Auth\Middleware\Authenticate::class,
            'localize'                => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
            'localizationRedirect'    => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect'   => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeCookieRedirect'    => \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            'localeViewPath'          => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
