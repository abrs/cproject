<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description'];
    protected $hidden = ['pivot'];

    #--------------------relations--------------------------------

    public function comments() {
        return $this->hasMany(Comment::class, 'task_id', 'id');
    }
    
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
            $builder->select(['tasks.*', 'lt.list_id', 'pl.project_id'])

                    ->join('lists_tasks as lt', 'tasks.id', '=', 'lt.task_id')
                    ->join('project_lists as pl', 'pl.id', '=', 'lt.list_id')
                    ->join('projects', 'pl.project_id', '=', 'projects.id')
                    ->where('projects.project_owner' , \Auth::user()->id);
        });
    }
}