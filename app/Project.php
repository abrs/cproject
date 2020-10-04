<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description'];
    
    public function projectLists() {
        return $this->hasMany(ProjectList::class);
    }

    public function addList($title, $description) {

        return $this->projectLists()->firstOrCreate(
            ['title'=> $title],
            ['description' => $description],
        );
    }

    /**
     * get project's users
     */
    public function users() {
        return $this->belongsToMany(User::class, 'members_projects', 'member_id', 'project_id')->withTimestamps();
    }

    /**
     * assign new memeber to a project
     */
    #NOTE: you can use toggle to attach or detach a member
    public function assignMember(int $member_id) {

        return $this->users()->attach($member_id);
    }
}
