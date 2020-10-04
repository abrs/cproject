<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\List_Task;
use App\Project;
use App\ProjectList;
use App\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProjectListsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projId = request()->project_id;
        $projects = ProjectList::where('project_id', $projId)->get();

        return response()->json(['projects' => $projects]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @uses   $request->title
     * @uses   $request->description
     * @uses   $request->project_id
     * @uses   $request->task_id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'title' => ['required'],
            'description' => ['min:3']
        ]);    
        
        $title = request()->title;
        $description = request()->description;
        
        try{
            $projId = request()->project_id;
            $project = Project::findOrFail($projId);

            #get the task id which the list belongs to.
            $taskId = request()->task_id;
            Task::findOrFail($taskId);

            #get the list id to which i want to assign a task
            $list =  $project->addList($title, $description);

            #use attach to create new list_task record
            $list->tasks()->attach($taskId);
            
            return response()->json([
                'list' => $list,
            ]);

        }catch(ModelNotFoundException $ex) {
            return response()->json(['error' => 'wrong task|project id!!']);
        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function show(ProjectList $projectList)
    {
        //TODO: only show tasks assigned to me.
        // return response()->json([
        //     'projectTasksAssignedToMe' => $projectList->tasks,
        // ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProjectList $projectList)
    {

        request()->validate([
            'title' => ['required'],
            'description' => ['min:3']
        ]);

        $title = request()->title;
        $desc = request()->description; 
        // $projId = request()->project_id;
        
       $updated =  $projectList->update([
           'title'=>  $title ,
           'description'=>  $desc ,
        //    'project_id'=>  $projId ,
        ]);

        return response()->json([
            'result' => $updated ? 'Project\'s list updated successfully.' : 'fail to update!!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProjectList $projectList)
    {
        $oldList = $projectList;
        $projectList->delete();
        return response()->json([
                'oldList'    => $oldList,
                'message'    => "list deleted successfully."
        ]);
    }

    public function addListToProject($project, Request $request)
    {
        $validatedProject = request()->validate([
            'title' => ['required'],
            'description' => ['min:3']
        ]);    

        $title = request()->title;
        $desc = request()->description; 
        
       $list =  ProjectList::create([
           'title'=>  $title ,
           'description'=>  $desc ,
           'project_id'=>  $project ,
        ]);

        return response()->json([
            'list' => $list,
            'message' => "Project list created successfully."
        ]);
    }
}
