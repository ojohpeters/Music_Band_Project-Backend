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
            // Validate purchase data
            $validatedData = $request->validate([
                'item_id' => 'required|integer',
                'item_type' => 'required|string',
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'card_number' => 'required|digits_between:13,19', // Card number: 13-19 digits
                'expiry_date' => 'required|date_format:m/y|after:today', // Expiry format MM/YY
                'cvv' => 'required|digits:3', // CVV: 3 digits
            ]);
    
            // Process payment and validate card details
            $paymentStatus = $this->processPayment($validatedData['card_number'], $validatedData['expiry_date'], $validatedData['cvv']);
    
            if (!$paymentStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed. Invalid card details.',
                ], 400);
            }
    
            // Save the purchase if payment is successful
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
    public function show(Request $purchase)
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

    /**
     * Process the purchase and validate card details.
     */
    public function processPurchase(Request $request)
    {
        // Validate the inputs, including card details
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id', // Ensure user exists
            'item_type' => 'required|string|max:255',
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'card_number' => 'required|digits_between:13,19', // Card number: 13-19 digits
            'expiry_date' => 'required|date_format:m/y|after:today', // Expiry format MM/YY
            'cvv' => 'required|digits:3', // CVV: 3 digits
        ]);

        // Save the purchase to the database
        $purchase = Purchases::create([
            'user_id' => $validated['user_id'],
            'item_type' => $validated['item_type'],
            'item_id' => $validated['item_id'],
            'quantity' => $validated['quantity'],
            'total_price' => $validated['total_price'],
        ]);

        // Simulate payment processing
        $paymentStatus = $this->processPayment($validated['card_number'], $validated['expiry_date'], $validated['cvv']);

        // Handle payment failure
        if (!$paymentStatus) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Invalid card details.',
            ], 400);
        }

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Payment successful!',
            'data' => $purchase,
        ], 201);
    }

    /**
     * Simulate payment processing by validating card details.
     */
    private function processPayment($cardNumber, $expiryDate, $cvv)
    {
        // Validate card number using the Luhn algorithm
        if (!$this->isValidCardNumber($cardNumber)) {
            return false; // Invalid card number
        }

        // Validate the expiry date
        $currentDate = now();
        [$month, $year] = explode('/', $expiryDate);
        $expiryDateObject = $currentDate->copy()->setDate(2000 + (int) $year, (int) $month, 1);

        if ($expiryDateObject->lessThanOrEqualTo($currentDate)) {
            return false; // Card expired
        }

        // Validate CVV (length already checked in validation rules)
        if (!is_numeric($cvv)) {
            return false; // Invalid CVV
        }

        return true; // Payment successful if all checks pass
    }

    /**
     * Validate card number using the Luhn algorithm.
     */
    private function isValidCardNumber($number)
    {
        $sum = 0;
        $alt = false;

        // Process each digit from right to left
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $alt = !$alt;
        }
        // Valid if the total modulo 10 is 0
        return ($sum % 10) === 0;
    }
}
