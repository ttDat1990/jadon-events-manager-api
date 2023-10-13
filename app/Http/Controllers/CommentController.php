<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
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

    public function index2(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = Comment::with(['user:id,email', 'pressReview:id,title']);

        $email = $request->input('email');
        if (!empty($email)) {
            $query->whereHas('user', function ($userQuery) use ($email) {
                $userQuery->where('email', 'like', "%$email%");                 
            });
        }
        $query->orderByRaw('isChecked ASC, created_at DESC');
        $comments = $query->paginate($perPage);
        return response()->json($comments);
    }

    public function uncheckedComment()
    {
        $count = Comment::where('isChecked', false)->count();

        return response()->json(['count' => $count]);
    }

    public function updateIsChecked($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not exist'], 404);
        }

        $comment->isChecked = true;
        $comment->save();

        return response()->json(['message' => 'Comment is checked','comment' => $comment]);
    }

    public function updateRowChecked(Request $request)
    {
        $commentIds = $request->input('comment_ids');

        if (empty($commentIds)) {
            return response()->json(['message' => 'No comment IDs provided'], 400);
        }

        $comments = Comment::whereIn('id', $commentIds)->get();

        if ($comments->isEmpty()) {
            return response()->json(['message' => 'No matching comments found'], 404);
        }

        $comments->each(function ($comment) {
            $comment->isChecked = true;
            $comment->save();
        });

        return response()->json(['message' => 'Comments updated successfully']);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'press_id' => 'required|exists:press_reviews,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $recaptchaResponse = $request->input('captchaValue');
        $recaptchaSecretKey = '6LcoRJMoAAAAAC9pqc1w0i5ouV8aIXqNAMVPPZzz';

        $response = Http::asForm()
        ->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $recaptchaSecretKey,
            'response' => $recaptchaResponse,
        ]);

        $responseData = $response->json();

        if (!$responseData['success']) {
            return response()->json(['message' => 'reCAPTCHA validation failed'], 400);
        }

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

    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not exist'], 404);
        }
        
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
