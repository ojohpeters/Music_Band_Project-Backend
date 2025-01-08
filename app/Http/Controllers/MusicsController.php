<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Musics;

class MusicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth('sanctum')->user();     
            if ($user) {
                $musics = Musics::all();
            } else {
                $musics = Musics::where('is_free', 1)->get();
            }
    
            return response()->json($musics, 200);
    
        } catch (\Exception $e) {
            return response()->json([
                "Error" => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                "title" => "required|string|max:255",
                "album" => "required|string|max:255",
                "artist" => "required|string|max:255",
                "price" => "required|numeric|min:0",
                "is_free" => "required|boolean",
                "file_path" => "required|file|mimes:mp3,wav|max:10240", // Limit to 10MB and specific audio formats
                "cover_image" => "nullable|image|mimes:jpeg,png,jpg|max:5120", // Image validation, max 5MB
            ]);
    
            // Handle file upload for music
            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    return response()->json([
                        'error' => 'File upload error',
                        'message' => $file->getError(),
                    ], 422);
                }
                $fileName = uniqid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('public/uploads/music', $fileName);
                \Log::info('Music file uploaded to: ' . $filePath); // Log the file path
                $validatedData['file_path'] = $fileName;
            }
    
            // Handle file upload for cover image
            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
                $validatedData['cover_image'] = $imageName;
            }
    
            // Create a new music record with the validated data
            $music = Musics::create($validatedData);
    
            // Return success message with created music details
            return response()->json([
                'message' => 'Music created successfully',
                'music' => $music,
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the music',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function show($id)
    {
        try {
            // Retrieve the music by ID
            $music = Musics::find($id);
    
            // Check if the music exists
            if (!$music) {
                return response()->json([
                    'error' => 'Music not found',
                ], 404);
            }
    
            return response()->json($music, 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'error' => 'An error occurred while retrieving the music',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

   
    
    public function edit(Musics $musics)
    {
        //
    }

   
    public function update(Request $request, Musics $musics)
    {
        try {
            $validatedData = $request->validate([
                "title" => "sometimes|required|string|max:255",
                "album" => "sometimes|required|string|max:255",
                "artist" => "sometimes|required|string|max:255",
                "price" => "sometimes|required|numeric|min:0",
                "is_free" => "sometimes|required|boolean",
                "file_path" => "sometimes|file|mimes:mp3,wav|max:10240", // Limit to 10MB
                "cover_image" => "sometimes|nullable|image|mimes:jpeg,png,jpg|max:5120", // Image validation
            ]);
    
            // Handle file upload for music
            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $fileName = uniqid() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/uploads/music', $fileName);
    
                if ($musics->file_path) {
                    $oldFilePath = storage_path('app/public/uploads/music/' . $musics->file_path);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
    
                $validatedData['file_path'] = $fileName;
            }
    
            // Handle file upload for cover image
            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
    
                if ($musics->cover_image) {
                    $oldImagePath = storage_path('app/public/uploads/images/' . $musics->cover_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
    
                $validatedData['cover_image'] = $imageName;
            }
    
            // Update the music record with the validated data
            $musics->update($validatedData);
    
            return response()->json([
                'message' => 'Music updated successfully',
                'music' => $musics,
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the music',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    

   
    public function destroy($id)
    {
        try {
            // Find the user by ID
            $music = Musics::find($id);
    
            // Check if the user exists
            if (!$music) {
                return response()->json([
                    'error' => 'music not found',
                ], 404);
            }
    
            // Delete the music
            $music->delete();
    
            return response()->json([
                'message' => 'music deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'error' => 'An error occurred while deleting the music',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
}
