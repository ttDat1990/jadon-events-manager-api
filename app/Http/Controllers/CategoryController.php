<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        $result = $categories->map(function ($category) {
            $category['img_url'] = asset($category['img_url']);
            return $category;
        });

        return response()->json($result);
    }
    
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category['img_url'] = asset($category['img_url']);

        return response()->json($category, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
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
            'img_url' => $relativeImageUrl,
        ]);

        return response()->json(['message' => 'Category created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'name' => 'required|unique:categories,name,'.$id,
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $category = Category::findOrFail($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->title = $request->input('title');
        $category->name = $request->input('name');

        if ($request->hasFile('image')) {

            // Xóa tệp hình ảnh cũ
            $oldImagePath = public_path($category->img_url);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $relativeImageUrl = 'images/' . $imageName;
            $category->img_url = $relativeImageUrl;
        }

        $category->save();

        return response()->json('Category updated successfully');
    }
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Xóa hình ảnh của danh mục nếu có
        if (file_exists(public_path($category->img_url))) {
            unlink(public_path($category->img_url));
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }



}
