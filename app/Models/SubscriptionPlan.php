<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'subscription_plans';

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
