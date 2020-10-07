<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description'];

    #----------------------------------------------------

    public function lists() {
        return $this->belongsToMany(ProjectList::class, 'lists_tasks' , 'task_id', 'list_id')->withTimestamps();
    }

    #----------------------------------------------------

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('', function (Builder $builder) {
            $builder->select(['tasks.id', 'tasks.title', 'tasks.description', 'tasks.created_at', 
                'tasks.updated_at', 'lists_tasks.list_id', 'project_lists.project_id'])

                    ->join('lists_tasks', 'tasks.id', '=', 'lists_tasks.task_id')
                    ->join('project_lists', 'project_lists.id', '=', 'lists_tasks.list_id')
                    ->join('projects', 'project_lists.project_id', '=', 'projects.id')
                    ->where('projects.project_owner' , \Auth::user()->id);
        });
    }
}