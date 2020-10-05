<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Project;
use App\ProjectList;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $project_owner = request()->project_owner;

        return response()->json([
            'projects' => Project::where('project_owner', $project_owner)->get(),
            // 'projects' => Project::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedProject = request()->validate([
            'name' => ['required'],
            'description' => ['min:3']
        ]);

        #firstOrCreate in an instance with the same name exists then pass creating new one, 
        #else create it with the specified name and description
        #that will make the name unique.        
        $project = Project::firstOrCreate(['name' => $request->name], 
            [
                'description' => $request->description,
                'project_owner' => $request->project_owner,
            ]);

        return response()->json([
                'project' => $project,
                'message' => "Project created successfully."
            ]);
    }

    private function getProjectAttributes($project) {
        return $project::with('projectLists.tasks')->get();
    }

    #get all tasks related to a project
    private function getProjectTasks(Project $project) {

        $tasks = collect();

        ProjectList::whereHas('tasks', function(Builder $query) use ($project) {
            $query->where('project_id', $project->id);
        })->get()->each(function($list) use ($tasks){
            $list->tasks->each(function($task) use ($tasks) {
                $tasks->push($task);
            });
        });

        return $tasks->unique('title');
    }    

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {        

        $project_owner = request()->project_owner;
        
        if($project->project_owner != $project_owner) {
            abort(403, 'Unauthorized action.');
        }

        $projectAttributes = $this->getProjectAttributes($project);

        return response()->json([
            'projectName'  => $projectAttributes->get(0)->name,
            'projectLists' => $projectAttributes->get(0)->projectLists->pluck('title'),
            'projectTasks' => $this->getProjectTasks($project)->pluck('title'),
            // 'projectMembers' => $project->members,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {

        $project_owner = request()->project_owner;
        
        if($project->project_owner != $project_owner) {
            abort(403, 'Unauthorized action.');
        }
        
        $validatedProject = request()->validate([
            'name' => ['required'],
            'description' => ['min:3']
        ]);

        $project = $project->update($validatedProject);

        return response()->json([
            'project' => $project,
            'message' => "project updated successfully."
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $oldProject = $project;
        $project->delete();
        return response()->json([
                'oldProject'    => $oldProject,
                'message'       => "project deleted successfully."
        ]);
    }

    /**
     * add member to a project
     */
    public function addMember(Project $project) {

        $member_id = request()->member_id;

        try {
            $project->assignMember($member_id);
        }catch(Exception $e) {
            return $e;
        }

        return response()->json(['message' => 'project\'s member created successfully...']);
    }

    #get users of a project
    public function getProjectMembers(Project $project) {
        return $project->users;
    }    
}
