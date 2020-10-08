<?php

namespace App;

use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

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
        return $this->belongsToMany(Project::class, 'members_projects', 'member_id', 'project_id')->withTimestamps();
    }

    /**
     * get user's tasks
     */
    public function tasks() {
        return $this->belongsToMany(Task::class, 'members_tasks', 'member_id', 'task_id')->withTimestamps();
    }

    /**
     * assign task to a user
     */
    public function assignTaskToUser(int $taskId) {

        try{
            
            #attach task to member.
            $attach = \DB::table('members_tasks')
                    ->where(['member_id' => $this->id, 'task_id' => $taskId])
                    ->count() == 0;


            if($attach)  {
                $this->tasks()->attach($taskId);
            }

            return 'task assigned successfully..';

        }catch(Exception $e) {
            return $e;
        }
    }
}
