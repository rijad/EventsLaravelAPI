<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


/**
 * Attendees can't exist on their own, 
 * they are always part of specific Event
 */

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user'];

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $attendees = $this->loadRelationships($event->attendees()->latest());

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        // associate attendee to it's parent Event
        $attendee = $this->loadRelationships($event->attendees()->create([
            'user_id' => $request->user_id
        ]));

        return new AttendeeResource($attendee);


        
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * 
     * $event je string jer nam taj podatak ne treba i zato ne koristimo Model kako ga nebismo pozivali iz baze a moramo 
     * ga uvrstiti jer je poslan u routi
     * 
     */
    
    public function destroy(string $event, Attendee $attendee)
    {
        //Checks if allowed to delete
        
        if(Gate::denies('attendee-delete', [$event, $attendee])){
            abort(403, 'You are not authorized to delete this attendee.');
        }
        $attendee->delete();
        return response(status: 204);
        
    }
}
