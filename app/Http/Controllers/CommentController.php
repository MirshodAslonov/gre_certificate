<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function listUserSubscription(Request $request, $user_id)
    {
        $list = Comment::where('user_id', $user_id)
            ->orderBy('created_at','desc')
            ->get();
        return response()->json($list);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string',
            'custom_date' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
        ]);
        Comment::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Comment successfully added',
            'comment' => $data,
        ]);
    }
}
