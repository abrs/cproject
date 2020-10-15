<?php

namespace App\Http\Controllers\API;

use App\Task;
use App\User;
use Exception;
use App\ProjectList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource which belong to certain project and certain list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $validator = \Validator::make(request()->all(), [
            'project_id' => ['required'],
            'list_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $projectId = request()->project_id;
        $listId = request()->list_id;
        // $tasks = collect();

        return Task::where('list_id' , $listId)
            ->where('project_id' , $projectId)            
            ->get()->unique('id');
    }

    #----------------------------------------------------

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @uses $request->title
     * @uses $request->description
     * @uses $request->list_id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'title' => ['required', 'min:3', 'max:20'],
            'description' => ['min:3'],
            'list_id' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        
        try{

            $list_id = $request->list_id;
            $list = ProjectList::findOrFail($list_id);
            
            #create the task record using title, description from the requset
            $task = $list->addTask($request->title, $request->has('description') ? $request->description : null);

            #attach list to task.
            $attach = \DB::table('lists_tasks')
                    ->where(['list_id' => $list_id, 'task_id' => $task->id])
                    ->count() == 0;


            if($attach)  {$task->lists()->attach($list_id);}
            
            return response()->json([
                'task' => $task,
            ]);

        }catch(ModelNotFoundException $ex) {
            return response()->json(['error' => 'wrong list id!!']);
        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }

    }

    #----------------------------------------------------

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(int $task)
    {
        //show all tasks which assigned to others with its different lists.
        $othersTasks = collect();

        ProjectList::has('tasks')->get()
        
            ->each(function($list) use ($othersTasks){

                $list->tasks()->join('members_tasks as mt', 'mt.task_id', '=', 'tasks.id')
                    ->where('mt.member_id', '!=', \Auth::user()->id)
                    ->get()
                    ->unique()
                    ->each(function($task) use ($othersTasks) {
                        $othersTasks->push($task);
                    });
            });

        return $othersTasks;
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $task)
    {

        try{
            #get the task by its id
            $task = Task::findOrFail($task);

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
        $desc = $request->has('description') ? $request->description : $task->description; 
        
       $updated =  $task->update([
           'title'=>  $title ,
           'description'=>  $desc ,
        ]);

        return response()->json([
            'result' => $updated ? 'task updated successfully.' : 'fail to update!!',
        ]);
    }

    #----------------------------------------------------

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $task)
    {

        try{
            #get the task by its id
            $task = ProjectList::findOrFail($task);

        }catch(Exception $e) {
            return response()->json(['error' => 'can\'t find your task']);
        }

        $oldTask = $task;
        $task->delete();
        
        return response()->json([
                'oldList'    => $oldTask,
                'message'    => "task deleted successfully."
        ]);
    }

    #----------------------------------------------------

    /**
     * assign task to a me (the authenticated user)
     * @uses getProjectTasksIDs: from ProjectController to get a task id 
     */
    public function assignTaskToMe() {

        try{
            
            $userId = \Auth::user()->id;
            $taskId = request()->task_id;

            $user = User::findOrFail($userId);
            $result = $user->assignTaskToUser($taskId);

            return response()->json(['result' => $result]);

        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }

    }

    #----------------------------------------------------

    /**
     * assign task to a user from inside the project
     * @uses getProjectMembersIDs: from ProjectController to get one of the users id
     * @uses getProjectTasksIDs: from ProjectController to get a task id 
     */
    public function assignTaskToMember() {
        
        try{
            $userId = request()->user_id;
            $taskId = request()->task_id;

            $user = User::findOrFail($userId);
            $result = $user->assignTaskToUser($taskId);

            return response()->json(['result' => $result]);

        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    #----------------------------------------------------

}
