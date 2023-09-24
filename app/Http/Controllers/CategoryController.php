<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
        public function index()
    {
        $categories = Category::with('images')->get();

        $result = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'title' => $category->title,
                'name' => $category->name,
                'image_url' => $category->images ? asset($category->images->image_url) : null,
            ];
        });

        return response()->json($result);
    }
    
    public function show($id)
    {
        $category = Category::with('images')->find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $result = [
            'id' => $category->id,
            'title' => $category->title,
            'name' => $category->name,
            'image_url' => $category->images ? asset($category->images->image_url) : null,
        ];

        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'name' => 'required|unique:categories',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();       
        $image->move(public_path('images'), $imageName);
        $relativeImageUrl = 'images/' . $imageName;

        $category = Category::create([
            'title' => $request->input('title'),
            'name' => $request->input('name'),
        ]);

        $newImage = $category->images()->create([
            'name' => $request->input('name'),
            'image_url' => $relativeImageUrl,
            'category_id' => $category->id,
            'user_id' => null,
            'event_id' => null,
            'slide_id' => null,
            'press_review_id' => null,
        ]);

        return response()->json(['message' => 'Category created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            $this->validate($request, [
                'title' => 'required',
                'name' => 'required|unique:categories,name,' . $id,
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $relativeImageUrl = 'images/' . $imageName;


                if ($category->images) {
                    $imagePath = $category->images->image_url;
        
                    if (Storage::exists($imagePath)) {
                        Storage::delete($imagePath);
                    }
                    $category->images->delete();                 
                }                  
                $category->images()->create([
                    'name' => $request->input('name'),
                    'image_url' => $relativeImageUrl,
                ]);
             
            }

            $category->update([
                'title' => $request->input('title'),
                'name' => $request->input('name'),
            ]);

            return response()->json(['message' => 'Category updated successfully'], 200);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

}
