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
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->orderBy('comments.created_at', 'desc')
            ->get();

        return response()->json(['comments' => $comments], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'press_id' => 'required|exists:press_reviews,id',
            'content' => 'required|string',
        ]);

        $comment = new Comment();
        $comment->user_id = auth()->user()->id;
        $comment->press_id = $validatedData['press_id'];
        $comment->content = $validatedData['content'];
        $comment->save();

        $comment->name = auth()->user()->name;

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment]);
    }
}
