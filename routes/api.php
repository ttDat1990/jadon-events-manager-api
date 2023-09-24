<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SlideController;

// Auth admin
Route::post('/admin/login', [AdminAuthController::class, 'adminLogin']);
Route::group(['middleware' => ['auth:admin']], function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'adminLogout']);
});

// Auth user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users', [AuthController::class, 'index']);

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
Route::put('events/{id}', [EventController::class, 'update']);
Route::delete('events/{id}', [EventController::class, 'destroy']);

//slides
// GET /api/slides: Lấy danh sách tất cả các slides.
// GET /api/slides/{id}: Lấy thông tin chi tiết của một slide theo ID.
// POST /api/slides: Tạo một slide mới.
// PUT /api/slides/{id}: Cập nhật thông tin của một slide theo ID.
// DELETE /api/slides/{id}: Xoá một slide theo ID.
Route::resource('slides', SlideController::class);