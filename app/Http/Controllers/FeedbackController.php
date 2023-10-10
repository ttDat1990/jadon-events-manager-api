<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedBack;


class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Feedback::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        $query->orderByRaw('isChecked ASC, created_at DESC');

        $feedbacks = $query->paginate($perPage);

        return response()->json($feedbacks);
    }

    public function uncheckedFeedback()
    {
        $count = Feedback::where('isChecked', false)->count();

        return response()->json(['count' => $count]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'content' => 'required|string',
        ]);

        $feedback = Feedback::create($validatedData);

        return response()->json($feedback, 201);
    }

    public function updateIsChecked($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not exist'], 404);
        }

        $feedback->isChecked = true;
        $feedback->save();

        return response()->json(['message' => 'Feedback is checked','feedback' => $feedback]);
    }

    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json(['message' => 'Feedback not exist'], 404);
        }

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted']);
    }
}
