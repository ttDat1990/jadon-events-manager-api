<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PressReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;


class PressReviewController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = PressReview::query();

        if ($request->has('name')) {
            $pressName = $request->input('name');
            $query->where('title', 'like', "%$pressName%");
        }

        if ($request->has('author')) {
            $author = $request->input('author');
            $query->where('author', 'like', "%$author%");
        }

        $query->orderBy('created_at', 'asc');
        $pressReview = $query->paginate($perPage);

        $pressReview->getCollection()->transform(function ($pressReview) {
            $pressReview['img_url'] = asset($pressReview['img_url']);
            return $pressReview;
        });

        return response()->json($pressReview);
    }
    
    public function show($id)
    {
        $pressReview = PressReview::find($id);
        if (!$pressReview) {
            return response()->json(['message' => 'Press-Review not found'], 404);
        }

        $pressReview['img_url'] = asset($pressReview['img_url']);

        return response()->json($pressReview, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();       
        $image->move(public_path('images'), $imageName);
        $relativeImageUrl = 'images/' . $imageName;

        $pressReview = PressReview::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'author' => $request->input('author'),
            'img_url' => $relativeImageUrl,
        ]);

        return response()->json('Press-review created successfully');
    }

    public function update(Request $request, $id)
    {
        $pressReview = PressReview::find($id);

        if (!$pressReview) {
            return response()->json(['message' => 'Press-review not found'], 404);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'author' => 'required|max:50',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $oldImage = $pressReview->img_url;

        $pressReview->title = $request->input('title');
        $pressReview->content = $request->input('content');
        $pressReview->author = $request->input('author');

        if ($request->hasFile('image')) {
            if ($oldImage) {
                $oldImagePath = public_path($oldImage);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $relativeImageUrl = 'images/' . $imageName;
            $pressReview->img_url = $relativeImageUrl;
        }

        $pressReview->save();

        return response()->json('Press-review updated successfully');
    }

    public function destroy($id)
    {
        $pressReview = PressReview::find($id);

        if (!$pressReview) {
            return response()->json(['message' => 'Press-review not found'], 404);
        }

        $imagePath = public_path($pressReview->img_url);

        if ($pressReview->img_url && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $pressReview->delete();

        return response()->json(['message' => 'Press-review deleted successfully'], 200);
    }


}
