<?php

namespace App\Http\Controllers\API;

use App\Task;
use Exception;
use App\Project;
use App\List_Task;
use App\ProjectList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectListsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $validator = \Validator::make(request()->all(), [
            'project_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $projId = request()->project_id;
        $projects = ProjectList::where('project_id', $projId)->get();

        return response()->json(['project lists' => $projects]);
    }

    #----------------------------------------------------

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
        $validator = \Validator::make($request->all(), [
            'title' => ['required'],
            'description' => ['min:3'],
            'project_id' => ['required'],
            'task_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        
        $title = $request->title;
        $description = $request->description;
        
        try{
            $projId = $request->project_id;
            $project = Project::findOrFail($projId);

            #get the task id which the list belongs to.
            $taskId = $request->task_id;
            Task::findOrFail($taskId);

            #get the list id to which i want to assign a task
            $list =  $project->addList($title, $description);
            #attach list to task.
            $attach = \DB::table('lists_tasks')
                    ->where(['list_id' => $list->id, 'task_id' => $taskId])
                    ->count() == 0;


            if($attach)  {
                #use attach to create new list_task record
                $list->tasks()->attach($taskId);
            }
            
            return response()->json([
                'list' => $list,
            ]);

        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    #----------------------------------------------------

    /**
     * Display the specified resource.
     *
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function show(int $projectList)
    {
        //only show tasks assigned to me.
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

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $projectList)
    {

        try{
            #get the project list by its id
            $projectList = ProjectList::findOrFail($projectList);

        }catch(Exception $e) {
            return response()->json(['error' => 'can\'t find your project']);
        }


        $validator = \Validator::make($request->all(), [
            'title'       => ['required'],
            'description' => ['min:3']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $title = $request->title;
        $desc  = $request->description; 
        // $projId = $request->project_id;
        
       $updated =  $projectList->update([
           'title'      =>  $title ,
           'description'=>  $desc ,
        //    'project_id'=>  $projId ,
        ]);

        return response()->json([
            'result' => $updated ? 'Project\'s list updated successfully.' : 'fail to update!!',
        ]);
    }

    #----------------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProjectList  $projectList
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $projectList)
    {
        try{
            #get the list by its id
            $projectList = ProjectList::findOrFail($projectList);

        }catch(Exception $e) {
            return response()->json(['error' => 'can\'t find your list']);
        }
        
        $oldList = $projectList;
        $projectList->delete();

        return response()->json([
                'oldList'    => $oldList,
                'message'    => "list deleted successfully."
        ]);
    }

    #----------------------------------------------------

    public function addListToProject($project, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title'       => ['required'],
            'description' => ['min:3']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }   

        $title = request()->title;
        $desc  = request()->description; 
        
        $list =  ProjectList::create([
           'title'      =>  $title ,
           'description'=>  $desc ,
           'project_id' =>  $project ,
        ]);

        return response()->json([
            'list'    => $list,
            'message' => "Project list created successfully."
        ]);
    }
}
