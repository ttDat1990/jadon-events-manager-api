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
        $slides = Slide::with('images')->get();

        // Chạy qua danh sách slides và thêm đường dẫn đầy đủ của hình ảnh
        $slides->each(function ($slide) {
        $slide->image_url = asset($slide->images->image_url);
    });

    return response()->json($slides);
    }

    public function show($id)
    {
        $slide = Slide::find($id);
        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }
        return response()->json($slide);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Lấy tệp hình ảnh từ yêu cầu
        $image = $request->file('image');

        // Đổi tên tệp hình ảnh
        $imageName = time() . '.' . $image->getClientOriginalExtension();

        // Di chuyển và lưu trữ tệp hình ảnh vào thư mục public/images
        $image->move(public_path('images'), $imageName);

        // Tạo một bản ghi mới trong bảng slides và lưu đường dẫn hình ảnh
        $slide = Slide::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'image_url' => 'images/' . $imageName,
        ]);

        // Tạo một bản ghi mới trong bảng images và liên kết nó với slide
        $imageRecord = new Image([
            'name' => $data['title'],
            'image_url' => 'images/' . $imageName,
        ]);

        $slide->images()->save($imageRecord);

        return response()->json(['message' => 'Slide created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $slide = Slide::find($id);
        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }

        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $slide->update($data);

        return response()->json($slide, 200);
    }

    public function destroy($id)
    {
        $slide = Slide::find($id);
        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }

        $slide->delete();

        return response()->json(['message' => 'Slide deleted'], 204);
    }
}
