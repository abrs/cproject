<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $fillable = ['body', 'user_id', 'task_id'];    

    #--------------------relations--------------------------------

    public function replies() {
        return $this->hasMany(Reply::class, 'comment_id', 'id');
    }

    #----------------------------------------------------

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    #----------------------------------------------------
    
    public function task() {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    #----------------------------------------------------
}
