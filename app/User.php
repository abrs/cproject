<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * get user's projects
     */
    public function projects() {
        return $this->belongsToMany(Project::class, 'members_projects', 'project_id', 'member_id')->withTimestamps();
    }

    /**
     * get user's tasks
     */
    public function tasks() {
        return $this->belongsToMany(Task::class, 'members_tasks', 'task_id', 'member_id')->withTimestamps();
    }

    /**
     * assign task to a user
     */
    public function assignTaskToUser(int $taskId) {

        return $this->tasks()->attach($taskId);
    }
}
