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

        $existingLike = Like::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existingLike) {

            $existingLike->delete();
            $message = 'Comment unliked successfully';
            $liked = false;
        } else {

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
