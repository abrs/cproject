<?php

namespace App\Http\Controllers\API;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Scopes\OwnerScope;
use App\ProjectList;
use App\Project;
use Exception;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return response()->json([
            'projects' => Project::all(),
        ]);
    }

    #----------------------------------------------------

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => ['required'],
            'description' => ['min:3'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        #firstOrCreate in an instance with the same name exists then pass creating new one, 
        #else create it with the specified name and description
        #that will make the name unique.
        $project = Project::firstOrCreate([
            'name' => $request->name, 
        ], 
        
        [
            'project_owner' => \Auth::user()->id,
            'description' => $request->description,
        ]);

        #attach me owner of the project.
        $attach = \DB::table('members_projects')
        ->where(['member_id' => \Auth::user()->id, 'project_id' => $project->id])
        ->count() == 0;


        if($attach)  {
            $project->users()->attach(\Auth::user()->id);
        }
            
        return response()->json([
                'project' => $project,
                'message' => "Project created successfully."
            ]);
    }

    #----------------------------------------------------

    private function getProjectAttributes(Project $project) {
        return $project::with('projectLists.tasks')->where('id', $project->id)->get();
    }

    #----------------------------------------------------

    #get all tasks related to a project
    public function getProjectTasks(Project $project) {

        $tasks = collect();

        #get all the lists whom have at least one task
        $listsWithTasks = ProjectList::whereHas('tasks', function(Builder $query) use ($project) {

            $query->where('project_id', $project->id);

        })->get();
        
        #for each list get its tasks
        $listsWithTasks->each(function($list) use ($tasks, $project){

            $list->tasks()->where(['pl.project_id' => $project->id, 'lt.list_id' => $list->id])->get()#;#->unique('list_id')
                ->each(function($task) use ($tasks) {
                    $tasks->push($task);
                });
        });

        return $tasks->sortBy('id');#

    }   
    
    #----------------------------------------------------
    #get all tasks related to a project
    public function getProjectTasksIDs(Project $project) {

        return response()->json(['project_tasks' => $this->getProjectTasks($project)->pluck('id')]);
    }   
    
    #----------------------------------------------------
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {        

        $projectAttributes = $this->getProjectAttributes($project);

        return response()->json([
            'projectName'  => $projectAttributes->get(0)->name,
            'projectLists' => $projectAttributes->get(0)->projectLists()->where('project_id', $project->id)->get(),#->pluck('title'),
            'projectTasks' => $this->getProjectTasks($project),#->pluck('title'),
            'projectMembers' => $project->users,#->pluck('name'),
        ]);
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $validator = \Validator::make($request->all(), [
            'name' => ['required'],
            'description' => ['min:3']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $project = $project->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'project' => $project,
            'message' => "project updated successfully."
        ]);
    }

    #----------------------------------------------------

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

    #----------------------------------------------------

    /**
     * add member to a project
     */
    public function addMember(Project $project) {

        try {
            
            $member_id = request()->member_id;
            $project->assignMember($member_id);

        }catch(Exception $e) {
            return $e;
        }

        return response()->json(['message' => 'project\'s member created successfully...']);
    }

    #----------------------------------------------------

    #get users of a project
    public function getProjectMembers(Project $project) {
        
        $projectMembers = collect();

        $project->users()->each(function($member) use ($projectMembers) {
            $projectMembers->add($member);
        });

        return response()->json(['project_members' => $projectMembers]);
    }

    #----------------------------------------------------

    /**
     * show all projects that shared with me (I am a participant in it and not its owner).
     */
    public function getSharedProjects() {
        
        $sharedWithMe = Project::withoutGlobalScope(OwnerScope::class)
            ->select('projects.*')
            #join projects and its members using project id
            ->join('members_projects as mp', 'projects.id', '=', 'mp.project_id')
            
            #get projects I am a participant in it
            ->where('mp.member_id', \Auth::user()->id)
            #execlude projects I am its owner
            ->where('projects.project_owner', '!=', \Auth::user()->id)
            ->get();

        return response()->json(['shared_projects' => $sharedWithMe]);
    }

    #----------------------------------------------------

    /**
     * show all my tasks, walk through each project tasks whom assigned to me.
     */
    public function getMyTasks() {
        
        $myTasks = collect();

        ProjectList::has('tasks')->get()
        
            ->each(function($list) use ($myTasks){

                $list->tasks()->join('members_tasks as mt', 'mt.task_id', '=', 'tasks.id')
                    ->where('mt.member_id', \Auth::user()->id)
                    ->get()
                    ->unique()
                    ->each(function($task) use ($myTasks) {
                        $myTasks->push($task);
                    });
            });

        return $myTasks;

    }

    
}
