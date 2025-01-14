<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Musics;
use Illuminate\Support\Facades\Log;
class MusicsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // $user = auth('sanctum')->user();     
            // if ($user) {
                $musics = Musics::all();
            // } else {
                // $musics = Musics::where('is_free', 1)->get();
            // }    
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
            $validatedData = $request->validate([
                "title" => "required|string|max:255",
                "album" => "required|string|max:255",
                "artist" => "required|string|max:255",
                "price" => "required|numeric|min:0",
                "is_free" => "required|boolean",
                "file_path" => "required|file|mimes:mp3,wav|max:10240", // Limit to 10MB and specific audio formats
                "cover_image" => "nullable|image|mimes:jpeg,png,jpg|max:5120", // Image validation, max 5MB
            ]);
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
                \Log::info('Music file uploaded to: ' . $filePath);
                $validatedData['file_path'] = $fileName;
            }
            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
                $validatedData['cover_image'] = $imageName;
            }
            $music = Musics::create($validatedData);
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
            $music = Musics::findOrFail($id);    
            // if ($music->is_free === 0) {    
                return response()->json([
                    "Music" => $music
                
                ], 200);    
            // } else {    
            //     return response()->json(['message' => 'Unauthorized access.'], 403);    
            // }    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {    
            return response()->json(['message' => 'Music not found.'], 404);    
        } catch (\Exception $e) {    
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);    
        }
    }  
    
    public function download($id)
    {
        try {
            $music = Musics::findOrFail($id);
            $filePath = public_path('storage/public/uploads/music/' . $music->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found', "Path" => $filePath], 404);
            }
    
            return response()->download($filePath, $music->title . '.mp3', [
                'Content-Type' => 'audio/mpeg',
                'Content-Disposition' => 'attachment; filename="' . $music->title . '.mp3"'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Download failed'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                "title" => "sometimes|required|string|max:255",
                "album" => "sometimes|required|string|max:255",
                "artist" => "sometimes|required|string|max:255",
                "price" => "sometimes|required|numeric|min:0",
                "is_free" => "sometimes|required|boolean",
                "file_path" => "sometimes|file|mimes:mp3,wav|max:10240", // Limit to 10MB and specific audio formats
                "cover_image" => "sometimes|nullable|image|mimes:jpeg,png,jpg|max:5120", // Image validation, max 5MB
            ]);
            $music = Musics::find($id);

            if (!$music){
                return response()->json([
                    "error" => "Invalid music id supplied"
                ]);
            }
    
            // Handle file uploads
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
    
            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
                $validatedData['cover_image'] = $imageName;
            }
            Log::info('Current music data:', $music->toArray());
            $music->update($validatedData);
            Log::info('Dirty attributes before save:', $music->getDirty());
            if ($music->isDirty()) {
                $music->save();
                return response()->json([
                    'message' => 'Music updated successfully',
                    'music' => $music,
                ], 200);
            } else {
                return response()->json(['message' => 'No changes were made'], 200);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Music not found', 'error' => $e->getMessage()], 404);
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
