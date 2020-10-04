<?php

namespace App;

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
}
