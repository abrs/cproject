<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectList extends Model
{
    //TODO: is project_id should be in the fillable array or it's dangerous because of mass assignment attack.
    protected $fillable = ['title', 'description', 'project_id'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function lists_tasks() {
        return $this->hasMany(List_Task::class, 'list_id');
    } 
    public function tasks() {
        return $this->belongsToMany(Task::class, 'lists_tasks' , 'list_id', 'task_id')->withTimestamps();
    } 
}
