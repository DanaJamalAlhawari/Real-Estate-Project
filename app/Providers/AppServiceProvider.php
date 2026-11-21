<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

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
        \Illuminate\Support\Facades\View::composer('partials.seeker-messages-link', function ($view) {
           $user = Auth::user();
            $unread = 0;

            if ($user instanceof \App\Models\RealEstateUser && $user->role === 'tenant') {
                $unread = \App\Models\Message::query()
                    ->whereIn('conversation_id', $user->tenantConversations()->select('conversations.id'))
                    ->where('sender_id', '!=', $user->getKey())
                    ->where('is_read', false)
                    ->count();
            }

            $view->with('unread', $unread);
        });
    }
}
