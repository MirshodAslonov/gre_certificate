<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    public function start(Request $request)
{
    $botToken = env('TELEGRAM_BOT_TOKEN');
    $data = $request->all();
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
            'password'    => $botToken
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
                        'url' => "http://10.128.158.61:5173/home?token={$token}"
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
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $groupId = env('TELEGRAM_GROUP_ID');

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
            $text = "✅ Siz allaqachon guruhdasiz!";
        } else {
            $inviteResponse = Http::post("https://api.telegram.org/bot{$botToken}/createChatInviteLink", [
                'chat_id' => $groupId,
                'member_limit' => 1
            ]);

            if ($inviteResponse->successful() && isset($inviteResponse->json()['result']['invite_link'])) {
                $groupLink = $inviteResponse->json()['result']['invite_link'];
                $text = "✅ Siz tizimga muvaffaqiyatli ulandingiz!\n" .
                        "👉 Guruhga qoʻshilish uchun havola (1 martalik):\n{$groupLink}";
            } else {
                $text = "✅ Siz tizimga ulandingiz, ammo guruh havolasi yaratilishda muammo bo‘ldi";
            }
        }

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }

    public function removeUserFromGroup(int $telegramId)
{
    $botToken = env('TELEGRAM_BOT_TOKEN');
    $groupId = env('TELEGRAM_GROUP_ID');

    $response = Http::post("https://api.telegram.org/bot{$botToken}/banChatMember", [
        'chat_id' => $groupId,
        'user_id' => $telegramId,
    ]);
    
    if ($response->successful()) {
        Http::post("https://api.telegram.org/bot{$botToken}/unbanChatMember", [
            'chat_id' => $groupId,
            'user_id' => $telegramId,
            'only_if_banned' => true
        ]);

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text'   => "❌ Siz guruhdan chiqarildingiz.",
        ]);

        return response()->json(['status' => 'success', 'message' => 'User removed from group']);
    }

    return response()->json(['status' => 'error', 'message' => 'Failed to remove user'], 500);
}

    public function check()
    {
        return response()->json(['status' => 'ok'], 200);
    }
}
