<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    public function index()
    {
        $slides = Slide::all();

        $slidesWithFullPath = $slides->map(function ($slide) {
            $slide['img_url'] = asset($slide['img_url']);
            return $slide;
        });

        return response()->json(['slides' => $slidesWithFullPath], 200);
    }

    public function show($id)
    {
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }

        $slide['img_url'] = asset($slide['img_url']);

        return response()->json(['slide' => $slide], 200);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            $slide = Slide::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'img_url' => 'images/' . $imageName,
            ]);

            $slide->save();

            return response()->json(['message' => 'Slide created successfully'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $slide = Slide::find($id);
            if (!$slide) {
                return response()->json(['message' => 'Slide not found'], 404);
            }

            $data = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // if ($data->fails()) {
            //     return response()->json(['message' => $validator->errors()], 400);
            // }

            $image = $request->file('image');

            if ($image) {

                $oldImagePath = public_path($slide->img_url);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }

                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $slide->img_url = 'images/' . $imageName;
            }

            $slide->title = $data['title'];
            $slide->content = $data['content'];
            $slide->save();

            return response()->json(['message' => 'Slide updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }

        $imagePath = public_path($slide->img_url);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $slide->delete();

        return response()->json(['message' => 'Slide deleted successfully'], 200);
    }
}
