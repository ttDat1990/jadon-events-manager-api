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
                'img_url' => 'images/' . $imageName,
            ]);

            $slide->save();

            return response()->json(['message' => 'Slide created successfully'], 201);
        } catch (\Exception $e) {
            // Xử lý lỗi ở đây
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Kiểm tra nếu slide tồn tại
            $slide = Slide::find($id);
            if (!$slide) {
                return response()->json(['message' => 'Slide not found'], 404);
            }

            $data = $request->validate([
                'title' => 'required|string',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Cho phép cập nhật ảnh, hoặc có thể là 'nullable'
            ]);

            // Lấy tệp hình ảnh từ yêu cầu
            $image = $request->file('image');

            // Nếu có tệp hình ảnh mới được cung cấp, thực hiện quá trình lưu trữ tương tự như trong hàm store
            if ($image) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
                $slide->img_url = 'images/' . $imageName;
            }

            // Cập nhật thông tin slide
            $slide->title = $data['title'];
            $slide->content = $data['content'];
            $slide->save();

            return response()->json(['message' => 'Slide updated successfully'], 200);
        } catch (\Exception $e) {
            // Xử lý lỗi ở đây
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        // Tìm slide cần xóa
        $slide = Slide::find($id);

        if (!$slide) {
            return response()->json(['message' => 'Slide not found'], 404);
        }

        // Xóa tệp hình ảnh
        $imagePath = public_path($slide->img_url);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Xóa slide khỏi cơ sở dữ liệu
        $slide->delete();

        return response()->json(['message' => 'Slide deleted successfully'], 200);
    }
}
