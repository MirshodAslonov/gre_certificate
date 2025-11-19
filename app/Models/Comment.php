<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'user_comments';
    protected $guarded = ['id']; 
    
}
