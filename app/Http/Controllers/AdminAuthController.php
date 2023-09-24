<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function adminLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

    $admin = Admin::where('email', $credentials['email'])->first();

    if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Tạo token và trả về response
    $token = $admin->createToken('admin_access_token')->plainTextToken;

    return response()->json(['admin' => $admin, 'token' => $token]);
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->user()->tokens()->delete();

        return response()->json(['message' => 'Admin logged out']);
    }
}
