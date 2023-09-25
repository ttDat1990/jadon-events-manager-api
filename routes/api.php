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
Route::post('events/{id}', [EventController::class, 'update']);
Route::delete('events/{id}', [EventController::class, 'destroy']);

//slides
Route::get('slides', [SlideController::class, 'index']);
Route::post('slides', [SlideController::class, 'store']);
Route::get('slides/{id}', [SlideController::class, 'show']);
Route::post('slides/{id}', [SlideController::class, 'update']);
Route::delete('slides/{id}', [SlideController::class, 'destroy']);
