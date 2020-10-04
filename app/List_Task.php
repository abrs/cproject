<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class List_Task extends Model
{
    protected $table = 'lists_tasks';
    protected $fillable = ['list_id', 'task_id'];

    public function tasks() {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function lists() {
        return $this->belongsTo(ProjectList::class, 'list_id', 'id');
    }
}
