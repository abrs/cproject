<?php

namespace App\Http\Controllers\API;

use App\Task;
use App\User;
use Exception;
use App\ProjectList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Database\Eloquent\ModelNotFoundException;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource which belong to certain project and certain list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        request()->validate([
            'title' => ['required', 'min:3', 'max:20'],
            'description' => ['min:3'],
        ]);

        $title = request()->title;
        $description = request()->description;

        try{
            $list_id = request()->list_id;
            ProjectList::findOrFail($list_id);
            
            #create the task record using title, description from the requset
            $task = Task::firstOrCreate(
                ['title' => $title], 
                ['description' => $description]
            );

            #attach list to task.
            $task->lists()->attach($list_id);
            
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
    public function show(Task $task)
    {
        //
    }

    #----------------------------------------------------

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        request()->validate([
            'title' => ['required'],
            'description' => ['min:3']
        ]);

        $title = request()->title;
        $desc = request()->description; 
        
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
    public function destroy(Task $task)
    {
        $oldTask = $task;
        $task->delete();
        
        return response()->json([
                'oldList'    => $oldTask,
                'message'    => "task deleted successfully."
        ]);
    }

    #----------------------------------------------------

    /**
     * assign task to a user from inside the project
     * @uses getProjectMembers: from ProjectController to get one of the users id
     * @uses getProjectTasks: from ProjectController to get a task id 
     */
    public function assignTaskToMember() {
        try{
            $userId = request()->user_id;
            $taskId = request()->task_id;

            $user = User::findOrFail($userId);
            $user->assignTaskToUser($taskId);
        }catch(Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    #----------------------------------------------------

}
