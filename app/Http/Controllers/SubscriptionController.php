<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function add(Request $request)
    {
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
}
