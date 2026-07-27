<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trusted proxies (for X-Forwarded-* / https detection) are configured
        // in AppServiceProvider::boot() from config('app.trusted_proxies') —
        // config isn't loaded yet when this closure runs, and '*' here would
        // let any client spoof its IP past the per-IP rate limiters.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\NoIndexNonProduction::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // 404s raised from inside a matched route (e.g. an unknown article slug hitting
        // firstOrFail) never reach the fallback routes, so render the same page here.
        // The route's middleware has run by this point, so Inertia's shared props exist.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($response->getStatusCode() !== 404
                || ! $request->route()
                || $request->expectsJson()
                || $request->is('admin/*')) {
                return $response;
            }

            return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
        });
    })->create();
