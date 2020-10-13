<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $fillable = ['body', 'user_id'];    

    #----------------------------------------------------

    public function replies() {
        return $this->hasMany(Reply::class, 'comment_id', 'id');
    }

    #----------------------------------------------------

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
