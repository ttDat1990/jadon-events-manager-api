<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Image;
use App\Models\AddOn;
use App\Models\User;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Event::with('images', 'add_ons', 'user', 'category')->orderBy('created_at', 'desc');

        $eventName = $request->input('event_name');
        if (!empty($eventName)) {
            $query->where('name', 'like', "%$eventName%");
        }

        $userName = $request->input('user_name');
        if (!empty($userName)) {
            $query->whereHas('user', function ($userQuery) use ($userName) {
                $userQuery->where('name', 'like', "%$userName%")
                    ->orWhereNull('name');
            });
        }

        $categoryName = $request->input('category_name');
        if (!empty($categoryName)) {
            $query->whereHas('category', function ($categoryQuery) use ($categoryName) {
                $categoryQuery->where('name', 'like', "%$categoryName%");
            });
        }

        $events = $query->paginate($perPage);

        return response()->json($events);
    }

    public function show($id)
    {
        $event = Event::with('images', 'add_ons', 'user', 'category')->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
            'add_ons' => 'array',
            'add_ons.*.department' => 'string',
            'add_ons.*.responsible' => 'string',
        ];

        $messages = [
            'images.*.image' => 'The :attribute must be an image.',
            'images.*.mimes' => 'The :attribute must be a file of type: jpeg, png, jpg, gif.',
            'images.*.max' => 'The :attribute may not be greater than 2048 kilobytes.',
        ];

        try {
            $validatedData = $request->validate($rules, $messages);

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
                        'event_id' => $event->id,
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

                $event->add_ons()->saveMany($addOns);
            }

            return response()->json('Event created successfully');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'string',
                'start_date' => 'date',
                'end_date' => 'date',
                'category_id' => 'exists:categories,id',
                'user_id' => 'exists:users,id',
                'images' => 'array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
                'add_ons' => 'array',
                'add_ons.*.department' => 'string',
                'add_ons.*.responsible' => 'string',
            ]);

            $event->update($validatedData);

            if ($request->hasFile('images')) {

                foreach ($event->images as $image) {
                    $imagePath = public_path('images/') . basename($image->image_url);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $event->images()->delete();

                $images = [];
                foreach ($request->file('images') as $index => $image) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('images'), $imageName);

                    $images[] = new Image([
                        'name' => $event->name . '_image' . $index,
                        'image_url' => asset('images/' . $imageName),
                        'event_id' => $event->id,
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

                $event->add_ons()->delete();
                $event->add_ons()->saveMany($addOns);
            }

            return response()->json('Event updated successfully');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);

            $images = $event->images;
            
            foreach ($images as $image) {
                $imagePath = public_path('images/') . basename($image->image_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $event->images()->delete();
            $event->add_ons()->delete();
            $event->delete();

            return response()->json('Event deleted successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserEvents(Request $request, $userId)
    {
        $perPage = $request->input('per_page', 10);
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not exist'], 404);
        }

        $query = $user->events()->with('images', 'category');

        if ($request->has('category_name')) {
            $categoryName = $request->input('category_name');
            $query->whereHas('category', function ($categoryQuery) use ($categoryName) {
                $categoryQuery->where('name', 'like', "%$categoryName%");
            });
        }

        $events = $query->paginate($perPage);

        return response()->json(['events' => $events], 200);
    }

}
