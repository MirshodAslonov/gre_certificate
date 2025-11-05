<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    public function start(Request $request)
{
    $botToken = config('services.telegram.bot_token');
    $data = $request->all();
    $frontendUrl = config('services.url.frontend');
    $telegramId = (string) ($data['message']['from']['id'] ?? null);

    if (isset($data['message']['chat']['type']) && $data['message']['chat']['type'] === 'supergroup') {
        return response()->json(['status' => 'ignored'], 200);
    }
    if (!$telegramId) {
        return response()->json(['error' => 'Telegram ID not found'], 200);
    }

    $user = User::where('telegram_id', $telegramId)->first();
    $exists = true;

    if (!$user) {
        $user = User::create([
            'telegram_id' => $telegramId,
            'status_id'   => 1,
            'role_id'     => 2,
            'telegram_first_name' => Arr::get($data, 'message.from.first_name', ''),
            'telegram_last_name'  => Arr::get($data, 'message.from.last_name', ''),
            'telegram_username'   => Arr::get($data, 'message.from.username', ''),
        ]);
        $exists = false;
        $text = "✅ Siz muvaffaqiyatli ro‘yxatdan o‘tdingiz\n
👇 Davom etish uchun quyidagi tugmani bosing.";
        $inline_text = '📝 Formani to‘ldirish';
    }else{
        $text = "Kirish uchun quyidagi tugmani bosing.";
        $inline_text = 'Kirish';
    }
    $token = $user->createToken('telegram_bot')->plainTextToken;
    
    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
        'chat_id' => $telegramId,
        'text' => $text,
       
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => $inline_text,
                        'url' => $frontendUrl."/home?token={$token}"
                    ]
                ]
            ]
        ])
    ]);

   
    return response()->json([
        'exists' => $exists,
        'token' => $token,
        'user' => $user
    ]);
}

    public function addUserToGroup(int $telegramId)
    {
        $botToken = config('services.telegram.bot_token');
        $groupId = config('services.telegram.group_id');

        $checkResponse = Http::get("https://api.telegram.org/bot{$botToken}/getChatMember", [
            'chat_id' => $groupId,
            'user_id' => $telegramId
        ]);

        $inGroup = false;
        if ($checkResponse->successful() && isset($checkResponse->json()['result']['status'])) {
            $status = $checkResponse->json()['result']['status'];
            if (in_array($status, ['member', 'administrator', 'creator'])) {
                $inGroup = true;
            }
        }

        if ($inGroup) {
            $text = "Siz allaqachon guruhdasiz!";
        } else {
            $inviteResponse = Http::post("https://api.telegram.org/bot{$botToken}/createChatInviteLink", [
                'chat_id' => $groupId,
                'member_limit' => 1
            ]);

            if ($inviteResponse->successful() && isset($inviteResponse->json()['result']['invite_link'])) {
                $groupLink = $inviteResponse->json()['result']['invite_link'];
                $text = $groupLink;
            } else {
                $text = "guruh havolasi yaratilishda muammo bo‘ldi";
            }
        }
            
        // Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
        //     'chat_id' => $telegramId,
        //     'text' => $text,
        //     'parse_mode' => 'HTML'
        // ]);
        return $text;
    }

    public function removeUserFromGroup(int $telegramId)
{
    $botToken = config('services.telegram.bot_token');
    $groupId = config('services.telegram.group_id');

    $response = Http::post("https://api.telegram.org/bot{$botToken}/banChatMember", [
        'chat_id' => $groupId,
        'user_id' => $telegramId,
    ]);
    $user_id = User::where('telegram_id', $telegramId)->first()->id;
    $invite_link  = UserSubscription::where('user_id',$user_id)
        ->orderBy('id','desc')
        ->first()
        ->invite_link;
    if ($response->successful()) {
        Http::post("https://api.telegram.org/bot{$botToken}/unbanChatMember", [
            'chat_id' => $groupId,
            'user_id' => $telegramId,
            'only_if_banned' => true
        ]);

        if ($invite_link) {
            Http::post("https://api.telegram.org/bot{$botToken}/revokeChatInviteLink", [
                'chat_id' => $groupId,
                'invite_link' => $invite_link,
            ]);

        }

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text'   => "❌ Siz guruhdan chiqarildingiz.",
        ]);

        return 'User removed from group';
    }

    return 'Failed to remove user';
}

    public function check()
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
