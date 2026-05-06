<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->withSchedule(function (Schedule $schedule) {
        $schedule->command('sidlan:sync')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        $schedule->command('sidlan:sync-progress')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        $schedule->command('gms:sync-albums')
            ->dailyAt('03:00')
            ->withoutOverlapping();
    })->create();
