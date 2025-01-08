<?php

namespace App\Http\Controllers;
use  Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function index(){
        try {    
            $authUser = auth()->user();    
            if (!$authUser) {
                return response()->json([
                    "Error" => "Unauthorized access. Please log in."
                ], 401);
            }
         if ($authUser->role === 'admin'){
            $users = User::all();
         }
         else {
            $users = User::where('role', 'user')->get();
         }
         return response()->json([
             "Users" => $users
         ], 200);
         // return $users;
        } catch (\Exception $e) {
         return response()->json([
             "Error" => $e->getMessage()
         ], 500);
        }
        }

    public function store(Request $request){
try {
    $validatedData = $request->validate([
        'name' => "required|max:255",
        'email' => "required|email|unique:users",
        'password' => 'required|min:8|confirmed',
        'role' => 'required'
    ]);
    unset($validatedData['password_confirmation']);
   $user =  User::create($validatedData);
    return response()->json([
        "Message" => "User created successfully",
        "User" => $user
        
    ], 200);
}
catch (\Exception $e) {
    return response()->json([
        "Error" => "Error creating user...",
        "Error_Message" => $e->getMessage()
    ], 500);
}
} 
public function login(Request $request) {
    try {
        $validatedData = $request->validate([
            "email" => "required|email",
            "password" => "required|min:8",
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth()->user();
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                "message" => "Logged in successfully",
                "user" => Auth::user(),
                "token" => $token
            ], 200);            
        }

        return response()->json([
            "message" => "Invalid credentials",
        ], 401);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            "errors" => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json([
            "message" => "An error occurred while processing your request.",
            "error" => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Retrieve the music by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ], 404);
        }

        return response()->json($user, 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return response()->json([
            'error' => 'An error occurred while retrieving the user',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ], 404);
        }

        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    } catch (\Exception $e) {
        // Handle any exceptions
        return response()->json([
            'error' => 'An error occurred while deleting the user',
            'message' => $e->getMessage(),
        ], 500);
    }
}
public function update(Request $request, $id)
{
    try {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Validate incoming data
        $validatedData = $request->validate([
            'name' => "sometimes|required|max:255",
            'email' => "sometimes|required|email|unique:users,email," . $user->id,
            'password' => 'sometimes|required|min:8|confirmed',
            'role' => 'sometimes|required'
        ]);

        // Handle password separately to hash it
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        // Update the user with the validated data
        $user->update($validatedData);

        return response()->json([
            "Message" => "User updated successfully",
            "User" => $user
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            "Error" => "User not found",
            "Error_Message" => $e->getMessage()
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            "Error" => "Error updating user...",
            "Error_Message" => $e->getMessage()
        ], 500);
    }
}

      
}
