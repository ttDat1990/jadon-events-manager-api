<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        return Review::all();
    }

    public function show($id)
    {
        return Review::find($id);
    }

    public function store(Request $request)
    {
        return Review::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);
        $review->update($request->all());
        return $review;
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        $review->delete();
        return ['message' => 'Review deleted'];
    }
}
