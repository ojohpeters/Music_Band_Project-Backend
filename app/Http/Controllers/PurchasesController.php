<?php

namespace App\Http\Controllers;

use App\Models\Purchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the user's purchases.
     */
    public function index()
    {
        try {
            $authUser = auth()->user();

            if (!$authUser) {
                return response()->json(['Error' => 'Unauthorized'], 401);
            }

            $purchases = Purchases::where('user_id', $authUser->id)->with('item')->get();

            return response()->json(['Purchases' => $purchases], 200);
        } catch (\Exception $e) {
            return response()->json(['Error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new purchase.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'item_id' => 'required|integer',
                'item_type' => 'required|string',
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
            ]);

            $validatedData['user_id'] = auth()->id();

            $purchase = Purchases::create($validatedData);

            return response()->json(['message' => 'Purchase created successfully', 'purchase' => $purchase], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a specific purchase.
     */
    public function show(Purchase $purchase)
    {
        try {
            if ($purchase->user_id !== auth()->id()) {
                return response()->json(['Error' => 'Unauthorized'], 403);
            }

            return response()->json(['Purchase' => $purchase->load('item')], 200);
        } catch (\Exception $e) {
            return response()->json(['Error' => $e->getMessage()], 500);
        }
    }
}
