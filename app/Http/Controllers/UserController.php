<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function list(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => 'nullable',
            'status_id' => 'nullable',
        ]);
        $users = User::when(isset($data['telegram_id']), function ($query) use ($data) {
            $query->where('telegram_id','like', '%'.$data['telegram_id'].'%')
                    ->orWhere('phone', 'like', '%'.$data['telegram_id'].'%');
        })
        ->when(isset($data['status_id']), function ($query) use ($data) {
            $query->where('status_id', $data['status_id']);
        })
        ->orderBy('id','desc')
        ->get();
        return response()->json($users);
    }

    public function get($id)
    {
        $user = User::where('id', $id)
        ->with('active_subscription')
        ->with('status')
        ->first();
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
        ]);
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']); 
        }
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User successfully updated',
            'user' => $user,
        ]);
    }
    public function adminUserUpdate(Request $request,int $user_id)
    {
        $user = User::findOrFail($user_id);
        
        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'status_id' => 'nullable|integer|exists:user_statuses,id',
        ]);
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']); 
        }
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
        $user->load('active_subscription');
        return response()->json($user);
    }

    public function getFreeDayLink(Request $request)
    {
        $user = $request->user();
        $user_subscription = UserSubscription::where('user_id',$user->id)
            ->first();
            if($user_subscription){
                return response()->json([
                    'free_day_link' => 'Siz allaqachon paketga ega bo‘lgansiz!',
                ]);
            }
        $user_subscription = UserSubscription::where('user_id',$user->id)
        ->where('total_amount',0)
        ->first();
        if($user_subscription){
            if($user_subscription->is_active){
                $freeDayLink = $user_subscription->invite_link;
            }else{
                $freeDayLink = "Siz bu paketdan foydalangansiz!";
            }
        }else{
            $text = (new BotController())->addUserToGroup($user->telegram_id);
            $freeDayLink = $text;
            SubscriptionService::addSubscription([
                'user_id' => Auth::id(),
                'started_at' => $data['started_at'] ?? now(),
                'expires_at' => $data['expires_at'] ?? now()->addDays(1),
                'invite_link' => $text 
            ]);
        }

        return response()->json([
            'free_day_link' => $freeDayLink,
        ]);
    }

    public function addUserToGroup(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:users,id',
        ]);
        $user = User::find($data['id']);
       
        $botController = new BotController();
        $text = $botController->addUserToGroup($user->telegram_id);

        return response()->json([
            'message' => $text,
        ]);
    }

    public function removeUserToGroup(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:users,id',
        ]);
        $user = User::find($data['id']);
        
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        if ($subscription) {

            // expires_at bugungi sanadan katta bo'lsa
            if ($subscription->expires_at > now()) {
                $subscription->expires_at = now();
                $subscription->update(['expires_at' => now()]);
            }

            // har holda active bo‘lganini o‘chirib qo‘yish
            $subscription->update(['is_active' => 0]);
            $user->update(['status_id' => 14]); // status_id ni 14 ga o‘zgartirish (Aktive obunasi tugaganlar)
        }
        $botController = new BotController();
        $text = $botController->removeUserFromGroup($user->telegram_id);

        return response()->json([
            'message' => $text,
        ]);
    }

    public function remindPayment(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:users,id',
        ]);
        $user = User::find($data['id']);
        $telegramId = $user->telegram_id;
        $paket = UserSubscription::where('user_id',$user->id)->where('is_active',1)->first();
        $payment_amount = ($paket->total_amount-$paket->paid_amount)/1000;
        $botToken = config('services.telegram.bot_token');
        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $telegramId,
            'text'   => 
"Assalomu alaykum 🌿

Sizning obunangiz bo‘yicha {$payment_amount } ming so‘m qoldiq mavjud.

Iltimos, o‘zingizga qulay vaqtda to‘lovni amalga oshirib qo‘ysangiz juda xursand bo‘lamiz 🙏

Agar savollar bo‘lsa: @aslonov_official
",
        ]);

        return response()->json([
            'message' => 'success'
        ]);
    }
}
