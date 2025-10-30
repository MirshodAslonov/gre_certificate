<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPhone extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'user_phone';


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
