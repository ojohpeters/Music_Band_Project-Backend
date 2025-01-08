<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;

class AdminController extends Controller
{
   public function users(){
   try {
    $users = User::where('role', 'admin')->get();
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
}
