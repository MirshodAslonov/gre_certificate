<?php

namespace App\Http\Controllers;

use App\Models\UserStatus;
use Illuminate\Http\Request;

class UserStatusController extends Controller
{
    public function userStatusList(Request $request)
{
    $data = UserStatus::select('user_statuses.*')
        ->withCount(['users']) // auto status_id bo‘yicha sanaydi
        ->get();

    return response()->json($data);
}
}
