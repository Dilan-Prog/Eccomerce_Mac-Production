<?php

namespace App\Providers;

use App\Listeners\RestoreCartOnLogin;
use App\Listeners\SyncCartOnLogout;
use App\Listeners\SyncCartToDatabase;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            RestoreCartOnLogin::class,
        ],
        Logout::class => [
            SyncCartOnLogout::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Eventos de texto que dispara anayarojo/shoppingcart en cada cambio
        // del carrito — no son clases, así que no van en $listen (ver
        // App\Listeners\SyncCartToDatabase).
        Event::listen('cart.added', [SyncCartToDatabase::class, 'handle']);
        Event::listen('cart.updated', [SyncCartToDatabase::class, 'handle']);
        Event::listen('cart.removed', [SyncCartToDatabase::class, 'handle']);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
