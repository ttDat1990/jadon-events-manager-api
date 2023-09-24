<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Image;
use App\Models\AddOn;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Event::with('images', 'add_on', 'user', 'category');

        if ($request->has('event_name')) {
            $eventName = $request->input('event_name');
            $query->where('name', 'like', "%$eventName%");
        }

        if ($request->has('category_name')) {
            $categoryName = $request->input('category_name');
            $query->whereHas('category', function ($categoryQuery) use ($categoryName) {
                $categoryQuery->where('name', 'like', "%$categoryName%");
            });
        }

        if ($request->has('user_name')) {
            $userName = $request->input('user_name');
            $query->whereHas('user', function ($userQuery) use ($userName) {
                $userQuery->where('name', 'like', "%$userName%");
            });
        }

        $events = $query->paginate($perPage);

        return response()->json($events);
    }

    public function show($id)
    {
        $event = Event::with('images', 'add_on', 'user', 'category')->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'images' => 'array', 
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', 
            'add_ons' => 'array', 
            'add_ons.*.department' => 'string',
            'add_ons.*.responsible' => 'string',
        ]);

        $event = Event::create($validatedData);
        $eventName = $validatedData['name'];
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
        
                $images[] = new Image([
                    'name' => $eventName . '_image' . $index,
                    'image_url' => asset('images/' . $imageName),
                    'user_id' => null,
                    'event_id' => $event->id,
                    'category_id' => null,
                ]);
            }

            $event->images()->saveMany($images);
        }

        if ($request->has('add_ons')) {
            $addOns = [];
            foreach ($request->input('add_ons') as $addOnData) {
                $addOns[] = new AddOn([
                    'department' => $addOnData['department'],
                    'responsible' => $addOnData['responsible'],
                    'event_id' => $event->id,
                ]);
            }
    
            $event->add_on()->saveMany($addOns);
        }

        return response()->json('Event created successfully');
    }

}
