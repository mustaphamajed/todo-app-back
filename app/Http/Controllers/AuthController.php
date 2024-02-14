<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PhpParser\Node\Stmt\TryCatch;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            // Validate user input
            $request->validate([
                'name' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'phone' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);
            // Create a new user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'firstname' => $request->input('firstname'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
            ]);
            Log::info('User registered successfully', ['user' => $user->id]);
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstErrorMessage = reset($errors)[0];
            Log::error('Validation failed during registration', ['error' => $firstErrorMessage]);
            return response()->json(['message' => 'Validation failed', 'error' => $firstErrorMessage], 422);
        }
    }
    /**
     * Login a user.
     */
    public function login(Request $request)
    {
        try {
            // Validate user input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            // Authenticate user
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                // Generate authentication token
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->makeVisible(['id', 'name', 'email', 'phone', 'firstname']);
                Log::info('Login successful', ['user' => $user->id]);
                return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token], 200);
            } else {
                throw ValidationException::withMessages(['email' => 'Invalid credentials']);
            }
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstErrorMessage = reset($errors)[0];
            Log::error('Login failed', ['error' => $firstErrorMessage]);
            return response()->json(['message' => 'Login failed', 'error' => $firstErrorMessage], 401);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function getAuthenticatedUser(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            if ($user) {
                Log::info('User retrieved successful', ['user' => $user->id]);

                return response()->json(['user' => $user], 200);
            } else {
                Log::error('User not found');
                return response()->json(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving user');

            return response()->json(['message' => 'Error retrieving user'], 500);
        }
    }

    /**
     * Get all users.
     */
    public function getAllUsers(Request $request)
    {

        try {
            // Fetch all users
            $users = User::all();
            return response()->json(['users' => $users], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching users: ' . $th->getMessage());
            return response()->json(['message' => 'Error fetching users', 'error' => $th->getMessage()], 500);
        }
    }
}
