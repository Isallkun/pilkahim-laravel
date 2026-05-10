<?php

namespace App\Providers;

use App\Services\AuditService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Audit semua auth event (login_success, logout, login_failed) lintas role.
        // Pakai closure (bukan class subscriber) — Laravel 11+ auto-discovery
        // bisa meng-register listener class 2x kalau pakai pola subscribe atau handle().
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $role = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;
            app(AuditService::class)->log($user, 'login_success', [
                'username' => $user->username ?? null,
                'name' => $user->name ?? null,
                'role' => $role,
                'guard' => $event->guard,
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) return;
            $user = $event->user;
            $role = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;
            app(AuditService::class)->log($user, 'logout', [
                'username' => $user->username ?? null,
                'name' => $user->name ?? null,
                'role' => $role,
                'guard' => $event->guard,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            app(AuditService::class)->log(null, 'login_failed', [
                'attempted_username' => $event->credentials['username'] ?? null,
                'guard' => $event->guard,
            ]);
        });
    }
}
