<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            \App\Services\LogService::record(
                type: 'auth',
                action: 'logout',
                description: 'Pengguna keluar (logout)',
                payload: null,
                userId: $event->user->id
            );
        }
    }
}
