<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use CanLoadRelationships;

    protected array $relations = ['user', 'attendees', 'attendees.user'];


    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');
    }

    public function index()
    {
        $query = $this->loadRelationships(Event::query());

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    public function store(Request $request)
    {
        $event = Event::create([
            ... $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'string|max:255|nullable',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]),
            'user_id' => $request->user()->id,
        ]);

        return new EventResource($this->loadRelationships($event));
    }


    public function show(Event $event)
    {
        return new EventResource($this->loadRelationships($event));
    }

    public function update(Request $request, Event $event)
    {
        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'string|max:255|nullable',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
            ])
        );
        return new EventResource($this->loadRelationships($event));
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
