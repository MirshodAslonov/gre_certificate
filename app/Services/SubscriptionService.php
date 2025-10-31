<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

/**
 * Class SubscriptionService
 * @package App\Services
 */
class SubscriptionService
{
    public static function addSubscription(array $data)
{
    $old_subscription = UserSubscription::where('user_id', $data['user_id'])
        ->update([
            'is_active' => false,
        ]);
    $user_subscription = UserSubscription::create([
        'user_id' => $data['user_id'],
        'started_at' => $data['started_at'] ?? now(),
        'expires_at' => $data['expires_at'] ?? now()->addDays(30), 
        'total_amount' => $data['total_amount'] ?? 0,
        'paid_amount' => $data['paid_amount'] ?? 0,
        'partial_payment_requested_at' => $data['partial_payment_requested_at'] ?? null,
        'invite_link' =>$data['invite_link'] ?? null
    ]);
    $botToken = config('services.telegram.bot_token');
    $telegramId = User::where('id', $data['user_id'])->first()->telegram_id;
    if(isset($data['total_amount']) && $data['total_amount'] > 0){
        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text' => $data['invite_link'],
            'parse_mode' => 'HTML'
        ]);
    }
    return $user_subscription;
}
}