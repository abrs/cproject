<?php

namespace App;

use Exception;
use App\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    #----------------------------------------------------

    public function projectLists() {
        return $this->hasMany(ProjectList::class);
    }

    #----------------------------------------------------

    public function addList($title, $description) {

        return $this->projectLists()->firstOrCreate(
            ['title'=> $title],
            ['description' => $description],
        );
    }

    #----------------------------------------------------

    /**
     * get project's users
     */
    public function users() {
        return $this->belongsToMany(User::class, 'members_projects', 'project_id', 'member_id')->withTimestamps();
    }

    #----------------------------------------------------

    /**
     * assign new memeber to a project
     * #NOTE: you can use toggle to attach or detach a member
     */
    public function assignMember(int $member_id) {

        #attach list to task.
        $attach = \DB::table('members_projects')
        ->where(['member_id' => $member_id, 'project_id' => $this->id])
        ->count() == 0;


        if($attach)  {
            return $this->users()->attach($member_id);
        }
    }

    #----------------------------------------------------

    protected static function booted()
    {
        static::addGlobalScope(new OwnerScope);
    }
}
