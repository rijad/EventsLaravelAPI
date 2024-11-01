<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
// add this to use $this->authorize()
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
{

    use CanLoadRelationships;
    

    private array $relations = ['user','attendees','attendees.user'];
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query =  $this->loadRelationships(Event::query(), $this->relations);

        return  EventResource::collection($query->latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' =>'nullable|string',
            'start_time'=>'required|date',
            'end_time'=>'required|date|after:start_time',
        ]);

        $data['user_id'] = $request->user()->id;

        $event = Event::create($data);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user','attendees'); // loads user data    
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //Checks if allowed to update
        // For  Gates:
        // if(Gate::denies('update-event', $event))
        // {
        //     abort(403, 'You are not authorized to update this event.');
        // } 
        // or use ->
        // $this->authorize('update-event', $event);
        // but add use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

        Gate::authorize('update',$event);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' =>'nullable|string',
            'start_time'=>'sometimes|date',
            'end_time'=>'sometimes|date|after:start_time',
        ]);

        $event->update($data);

        return new EventResource($this->loadRelationships($event)) ;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        Gate::authorize('delete',$event);
        $event->delete();

        return response(status: 204);
    }
}
