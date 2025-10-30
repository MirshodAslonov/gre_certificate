<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User successfully updated',
            'user' => $user,
        ]);
    }

    public function authGet(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }
}
