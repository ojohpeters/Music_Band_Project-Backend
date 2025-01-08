<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * Display a listing of Events.
     */
    public function index()
    {
        try {
            $Events = Events::all();

            return response()->json([
                'message' => 'Events retrieved successfully',
                'Events' => $Events,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while retrieving Events',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created Events in the database.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'date' => 'required|date',
                'location' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                 "cover_image" => "sometimes|nullable|image|mimes:jpeg,png,jpg|max:5120",
            ]);
            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
                $validatedData['cover_image'] = $imageName;
            }

            $Events = Events::create($validatedData);

            return response()->json([
                'message' => 'Events created successfully',
                'Events' => $Events,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the Events',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified Events.
     */
    public function show($id)
    {
        try {
            // Retrieve the event by ID
            $event = Events::find($id);
    
            // Check if the event exists
            if (!$event) {
                return response()->json([
                    'error' => 'event not found',
                ], 404);
            }
    
            return response()->json($event, 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'error' => 'An error occurred while retrieving the event',
                'message' => $e->getMessage(),
            ], 500);
        }
    
    
    }

    /**
     * Update the specified Events in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            $event = Events::find($id);
            if (!$event){
                return response()->json([
                    "Error" => "Invalid Event id"
                ]);
            }
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'location' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
            ]);

            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = uniqid() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/uploads/images', $imageName);
    
                if ($event->cover_image) {
                    $oldImagePath = storage_path('app/public/uploads/images/' . $event->cover_image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $validatedData['cover_image'] = $imageName;
            }        
            $event->update($validatedData);
            return response()->json([
                'message' => 'event updated successfully',
                'event' => $event,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the Events',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified Events from the database.
     */
    public function destroy($id)
    {
        try {
            $event = Events::find($id);

            if (!$event){
                return response()->json([
                    "Error" => "Event not found or invalid"
                ]);
            }
            $event->delete();

            return response()->json([
                'message' => 'Events deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the Events',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
