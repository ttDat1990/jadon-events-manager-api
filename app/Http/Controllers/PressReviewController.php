<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PressReview;
use Illuminate\Support\Facades\Storage;


class PressReviewController extends Controller
{
    public function index()
    {
        $pressReviews = PressReview::all();

        $result = $pressReviews->map(function ($pressReview) {
            $pressReview['img_url'] = asset($pressReview['img_url']);
            return $pressReview;
        });

        return response()->json($result);
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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

        return response()->json(['message' => 'Press-review created successfully'], 201);
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
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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

        return response()->json(['message' => 'Press-review updated successfully'], 200);
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
