<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();
        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($user),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,user',
                'phone' => 'nullable|string|max:15|unique:users,phone',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }

        // Hash the password before storing it
        $request->merge(['password' => bcrypt($request->password)]);

        // // Handle file upload if a profile picture is provided
        // if ($request->hasFile('profile_picture')) {
        //     $imagePath = $request->file('profile_picture')->store('images/users', 'public');
        //     $imageUrl = asset('storage/' . $imagePath);
        //     $request->merge(['profile_picture' => $imageUrl]);
        // }

        // Handle file upload if an image is provided
        if ($request->hasFile('picture')) {
            $imagePath = $request->file('picture')->store('images/users', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $request->merge(['profile_picture' => $imageUrl]);
        }

        $user = User::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ])->setStatusCode(201, 'Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user),
        ])->setStatusCode(200, 'OK');
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'role' => 'required|in:admin,user',
                'phone' => 'nullable|string|max:15|unique:users,phone,' . $user->id,
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
        // Hash the password before storing it
        if ($request->has('password')) {
            $request->merge(['password' => bcrypt($request->password)]);
        }
        // Handle file upload if a profile picture is provided
        if ($request->hasFile('picture')) {
            // Delete the old profile picture if it exists
            if ($user->profile_picture) {
                $oldImagePath = str_replace(asset('storage/'), '', $user->profile_picture);
                Storage::disk('public')->delete($oldImagePath);
            }
            // Store the new profile picture

            $imagePath = $request->file('picture')->store('images/users', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $request->merge(['profile_picture' => $imageUrl]);
        }
        $user->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ])->setStatusCode(200, 'OK');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Delete the profile picture if it exists
        if ($user->profile_picture) {
            $oldImagePath = str_replace(asset('storage/'), '', $user->profile_picture);
            Storage::disk('public')->delete($oldImagePath);
        }
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully',
        ])->setStatusCode(200, 'OK');
    }
}
