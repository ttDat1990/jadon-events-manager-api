<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index($pressId)
    {
        $comments = Comment::select('comments.*', 'users.name')
            ->where('press_id', $pressId)
            ->leftJoin('users', 'comments.user_id', '=', 'users.id')
            ->orderBy('comments.created_at', 'desc')
            ->get();

        foreach ($comments as $comment) {
            $comment->likes_count = $comment->likesCount();
        }
        
        return response()->json(['comments' => $comments], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'press_id' => 'required|exists:press_reviews,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Kiểm tra xem số tầng bình luận đã đạt đến giới hạn 5 chưa
        if ($validatedData['parent_id']) {
            $parentComment = Comment::find($validatedData['parent_id']);
            if ($parentComment->level >= 4) {
                return response()->json(['message' => 'Maximum nested comments level reached'], 400);
            }
        }

        $comment = new Comment();
        $comment->user_id = auth()->user()->id;
        $comment->press_id = $validatedData['press_id'];
        $comment->content = $validatedData['content'];

        // Nếu là bình luận con, cập nhật level và parent_id
        if ($validatedData['parent_id']) {
            $comment->parent_id = $validatedData['parent_id'];
            $comment->level = $parentComment->level + 1;
        } else {
            $comment->parent_id = null;
            $comment->level = 0;
        }

        $comment->save();

        $comment->name = auth()->user()->name;
        $comment->likes_count = 0;

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment]);
    }
}
