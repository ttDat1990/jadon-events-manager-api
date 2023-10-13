<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\PressReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ReviewController;

// Auth admin
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);
Route::group(['middleware' => ['auth:admin']], function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'adminLogout']);
});

// user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users', [AuthController::class, 'index']);
Route::delete('/users/{id}', [AuthController::class, 'destroy']);
Route::post('/users/{id}', [AuthController::class, 'update']);

//categories
Route::get('categories', [CategoryController::class, 'index']);
Route::post('categories', [CategoryController::class, 'store']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::post('categories/{id}', [CategoryController::class, 'update']);
Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

//events
Route::get('events', [EventController::class, 'index']);
Route::get('events/{id}', [EventController::class, 'show']);
Route::post('events', [EventController::class, 'store']);
Route::post('events/{id}', [EventController::class, 'update']);
Route::delete('events/{id}', [EventController::class, 'destroy']);
Route::get('user-events/{userId}', [EventController::class,'getUserEvents'])->middleware('auth:sanctum');

//slides
Route::get('slides', [SlideController::class, 'index']);
Route::post('slides', [SlideController::class, 'store']);
Route::get('slides/{id}', [SlideController::class, 'show']);
Route::post('slides/{id}', [SlideController::class, 'update']);
Route::delete('slides/{id}', [SlideController::class, 'destroy']);

//Press-reviews
Route::get('press-review', [PressReviewController::class, 'index']);
Route::get('press-review/{id}', [PressReviewController::class, 'show']);
Route::post('press-review', [PressReviewController::class, 'store']);
Route::post('press-review/{id}', [PressReviewController::class, 'update']);
Route::delete('press-review/{id}', [PressReviewController::class, 'destroy']);

//comments
Route::get('comment/{id}', [CommentController::class, 'index']);
Route::get('comment', [CommentController::class, 'index2']);
Route::get('/comment/unchecked/count', [CommentController::class, 'uncheckedComment']);
Route::get('/comment/check/{id}', [CommentController::class, 'updateIsChecked']);
Route::post('/comment/checks', [CommentController::class, 'updateRowChecked']);
Route::delete('/comment/{id}', [CommentController::class, 'destroy']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('comment', [CommentController::class, 'store']);
    Route::post('comment/{id}', [CommentController::class, 'update']);
});

//like
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('like/{commentId}', [LikeController::class, 'like']);
});

//contacts
Route::get('/contacts', [ContactController::class, 'index']);
Route::get('/contacts/unchecked/count', [ContactController::class, 'uncheckedContact']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/contacts/{id}', [ContactController::class, 'updateIsChecked']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);


//feedback
Route::get('/feedbacks', [FeedbackController::class, 'index']);
Route::get('/feedbacks/unchecked/count', [FeedbackController::class, 'uncheckedFeedback']);
Route::post('/feedbacks', [FeedbackController::class, 'store']);
Route::get('/feedbacks/{id}', [FeedbackController::class, 'updateIsChecked']);
Route::delete('/feedbacks/{id}', [FeedbackController::class, 'destroy']);

//reviews
Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{id}', [ReviewController::class, 'show']);
Route::post('reviews', [ReviewController::class, 'store']);
Route::post('reviews/{id}', [ReviewController::class, 'update']);
Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);