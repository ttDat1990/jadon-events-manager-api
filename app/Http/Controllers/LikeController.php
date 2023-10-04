<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Comment;

class LikeController extends Controller
{
    public function like(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user = auth()->user();

        // Kiểm tra xem người dùng đã thích bình luận này trước đó chưa
        $existingLike = Like::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existingLike) {
            // Nếu đã thích trước đó, xóa lượt thích để chuyển về trạng thái "chưa thích"
            $existingLike->delete();
            $message = 'Comment unliked successfully';
            $liked = false;
        } else {
            // Tạo một lượt thích mới cho bình luận
            $like = new Like();
            $like->user_id = $user->id;
            $like->comment_id = $comment->id;
            $like->save();
            $message = 'Comment liked successfully';
            $liked = true;
        }

        $likesCount = $comment->likesCount();

        return response()->json(['message' => $message, 'liked' => $liked, 'likes_count' => $likesCount]);
    }
}
