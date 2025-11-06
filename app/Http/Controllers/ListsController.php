<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class ListsController extends Controller
{
    public function allUsersList(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => 'nullable',
        ]);
        $users = User::when(isset($data['telegram_id']), function ($query) use ($data) {
            $query->where('telegram_id','like', '%'.$data['telegram_id'].'%');
        })
        ->orderBy('id','desc')
        ->get();
        return response()->json($users);
    }

    public function activeUsersList(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => 'nullable',
        ]);
        $subscriptions = UserSubscription::where('is_active',1)
            ->where('paid_amount','>',0)
            ->whereHas('user', function($query) use ($data) {
                if (isset($data['telegram_id'])) {
                    $query->where('telegram_id','like', '%'.$data['telegram_id'].'%');
                }
            })
            ->with('user')
            ->get();
        return response()->json($subscriptions);
    }

    public function debtorUsersList(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => 'nullable',
        ]);

        $subscriptions = UserSubscription::whereColumn('total_amount','>','paid_amount')
            ->whereHas('user', function($query) use ($data) {
                if (isset($data['telegram_id'])) {
                    $query->where('telegram_id','like', '%'.$data['telegram_id'].'%');
                }
            })
            ->with('user')
            ->get();
        return response()->json($subscriptions);
    }

    public function expiredUsersList(Request $request)
    {
        $data = $request->validate([
            'telegram_id' => 'nullable',
        ]);

        $today = now();
        $subscriptions = UserSubscription::where('expires_at', '<=', $today)
            ->where('is_active',1)
            ->whereHas('user', function($query) use ($data) {
                if (isset($data['telegram_id'])) {
                    $query->where('telegram_id','like', '%'.$data['telegram_id'].'%');
                }
            })
            ->with('user')
            ->get();
        return response()->json($subscriptions);
    }

}
