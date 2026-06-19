<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        $method = 'email';
        
        if (request()->routeIs('whatsapp.login') || request()->is('login/whatsapp/*')) {
            $method = 'whatsapp';
        } elseif (request()->routeIs('users.impersonate')) {
            $method = 'impersonate';
        } elseif (request()->routeIs('autologin')) {
            $method = 'autologin_link';
        }

        \App\Services\LogService::record(
            type: 'auth',
            action: 'login',
            description: "Pengguna berhasil masuk (login) via {$method}",
            payload: ['method' => $method],
            userId: $event->user->id
        );
    }
}
