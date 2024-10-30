<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Gate;
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
        //
        Gate::define('update-event',function($user, Event $event){
            //checks if logged in user is event createor
            return $user->id === $event->user_id;

        });

        Gate::define('delete-attendee',function($user,Event $event, Attendee $attendee){
            return 
                $user->id === $event->user_id || 
                $user->id === $attendee->user_id;
        });
        
    }
}
