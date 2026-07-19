<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller

{
    //
    // public function register(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'number' => 'required|string|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //     ]);

    //     $user = \App\Models\User::create([
    //         'name' => $validatedData['name'],
    //         'number' => $validatedData['number'],
    //         'password' => bcrypt($validatedData['password']),
    //     ]);

    //     return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    // }
    // public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'number' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     if (!auth()->attempt($credentials)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     $user = auth()->user();
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json(['message' => 'Login successful', 'access_token' => $token, 'token_type' => 'Bearer']);
    // }


    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        

        

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'number' => $validatedData['number'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'student',

        ]);
        $student = $user->student()->create([
            'last_name' => $validatedData['last_name'],
            'mother_name' => $validatedData['mother_name'],
            'father_name' => $validatedData['father_name'],
            'home_address' => $validatedData['home_address'],
            'goal' => $validatedData['goal'],
            'college' => $validatedData['college'],
            'path' => $validatedData['path'],
            'start_page' => $validatedData['start_page'] ,
            'end_page' => $validatedData['end_page'] ?? 0,
'days_of_memorization' => $validatedData['days_of_memorization'],
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

public function login(Request $request)
{
    $request->validate([
        'number' => 'required|string',
        'password' => 'required|string'
    ]);

    $user = User::where('number', $request->number)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'access_token' => $token,
        'role' => $user->role
    ]);
}
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

}

