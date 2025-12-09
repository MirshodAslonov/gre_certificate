<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function add(Request $request)
    {
        if($request->user()->role_id != 1){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        $data = $request->validate([
            "user_id" => "required|exists:users,id",
            'total_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'partial_payment_requested_at' => 'nullable|date',
        ]);
        $telegramId = User::where('id', $data['user_id'])->first()->telegram_id;
        $data['invite_link'] = (new BotController())->addUserToGroup($telegramId);
        $res = SubscriptionService::addSubscription($data);
        return response()->json($res);
    }

    public function listExpires(Request $request)
    {
        $today = now();
        $subscriptions = UserSubscription::where('expires_at', '<=', $today)
            ->where('is_active',1)
            ->with('user')
            ->get();
        return response()->json($subscriptions);
    }

    public function listUserSubscription($user_id)
    {
        $subscriptions = UserSubscription::where('user_id', $user_id)
            ->with('user')
            ->orderBy('created_at','desc')
            ->get();
        return response()->json($subscriptions);
    }

}
