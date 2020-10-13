<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $table = 'replies';
    protected $fillable = ['body', 'comment_id'];

    #----------------------------------------------------

    public function comment() {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }
}
