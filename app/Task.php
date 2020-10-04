<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description'];

    public function lists() {
        return $this->belongsToMany(ProjectList::class, 'lists_tasks' , 'task_id', 'list_id')->withTimestamps();
    }
}