<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'phone' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'firstname' => $request->input('firstname'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstErrorMessage = reset($errors)[0];

            return response()->json(['message' => 'Validation failed', 'error' => $firstErrorMessage], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->makeVisible(['id', 'name', 'email', 'phone', 'firstname']);
                return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token], 200);
            } else {
                throw ValidationException::withMessages(['email' => 'Invalid credentials']);
            }
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Login failed', 'errors' => $e->errors()], 401);
        }
    }
}
