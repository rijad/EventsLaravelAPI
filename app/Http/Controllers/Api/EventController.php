<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query =  Event::query();

        // relation contrstraints that can be generated and passed by user
        $relations = ['user','attendees','attendees.user'];

        //loop through all valid relations and if found add to query
        foreach($relations as $relation){
            $query->when(
                $this->shouldIncludeRelation($relation),
                fn($q) => $q->with($relation)

            );
        }

        return  EventResource::collection($query->latest()->paginate());
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');
        if (!$include){
            return false;
        }
        //trim spaces from array
        $relations = array_map('trim', explode(',',$include));
        return in_array($relation, $relations);
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

        $data['user_id'] = 1;

        $event = Event::create($data);

        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user','attendees'); // loads user data    
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' =>'nullable|string',
            'start_time'=>'sometimes|date',
            'end_time'=>'sometimes|date|after:start_time',
        ]);

        $event->update($data);

        return new EventResource($event) ;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
