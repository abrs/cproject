<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProjectList extends Model
{
    //TODO: is project_id should be in the fillable array or it's dangerous because of mass assignment attack.
    protected $fillable = ['title', 'description', 'project_id'];

    #----------------------------------------------------

    public function project() {
        return $this->belongsTo(Project::class);
    }

    #----------------------------------------------------

    public function tasks() {
        return $this->belongsToMany(Task::class, 'lists_tasks' , 'list_id', 'task_id')->withTimestamps();
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
            $builder->select('project_lists.*')
                    ->join('projects', 'projects.id', '=', 'project_lists.project_id')
                    ->where('projects.project_owner', \Auth::user()->id);
        });
    }
}
