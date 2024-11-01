<?php

namespace App\Console\Commands;

use App\Notifications\EventRemainderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventRemainders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-remainders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifacions to all event attendees that event starts soon!';

    /**
     * Execute the console command.
     */
    public function handle()
    {   
        $events = \App\Models\Event::with('attendees.user')
            ->whereBetween('start_time',[now(),now()->addDay()])
            ->get();
        $eventCount= $events->count();
        $eventLabel= Str::plural('event',$eventCount);
        
        $this->info("Found {$eventCount} {$eventLabel}");


        $events->each(fn ($event) => $event->attendees->each
                        (fn ($attendee) =>
                                $attendee->user->notify(
                                    new EventRemainderNotification(
                                        $event
                                    )
                                )    
                                //$this->info("Notifying the user {$attendee->user_id}")
                        )
                    );

        $this->info('Remainder notification sent successfully');
    }
}
