<?php

use Illuminate\Support\Facades\Route;
use API\ProjectListsController;
use Illuminate\Http\Request;
use API\ProjectController;
use API\TasksController;
use API\CommentsController;
use API\RepliesController;
use API\UsersController;
use App\Image;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'auth'], function () {

    #login a user into the system 
    Route::post('login', 'API\UsersController@login');
    #register a user into the system 
    Route::post('signup', 'API\UsersController@store');

    Route::group(['middleware' => 'auth:api'], function() {
    
        #logout route
        Route::get('logout', 'API\UsersController@logout');
        #user route
        Route::get('user', 'API\UsersController@user'); 
        #post an image if authenticated
        Route::post('post-image', 'API\UsersController@uploadImage');
        #get all uploaded images to update a user image using the image_url
        Route::get('posted-images', function() {return response()->json(['uploaded_images' => Image::all()]);});
    });
    
});


Route::group(['middleware' => 'auth:api'], function() {
    

    Route::group(['prefix' => 'projects'], function() {
    
        #assign task to a member route
        Route::post('/tasks/assign-to-me', 'API\TasksController@assignTaskToMe'); #2-1
        
        #assign task to a member route
        Route::post('/tasks/assign-member', 'API\TasksController@assignTaskToMember'); #2-2
        
        #get shared projects
        Route::get('/all-shared-projects', 'API\ProjectController@getSharedProjects'); #4
        
        #get project tasks iDs route
        Route::get('/my-tasks', 'API\ProjectController@getMyTasks'); #5
        
        #get project tasks
        // Route::get('/{project}/detailed-tasks', 'API\ProjectController@getProjectTasks'); #6
        
        #add member to a project route
        Route::post('/{project}/new-member', 'API\ProjectController@addMember'); #1        

        #get project members route
        Route::get('/{project}/members', 'API\ProjectController@getProjectMembers'); #2-2

        #get project tasks iDs route
        Route::get('/{project}/tasks', 'API\ProjectController@getProjectTasks'); #2-1, 6
        
        #single project.lists api route
        Route::post('/{project}/projectLists', 'API\ProjectListsController@addListToProject');

    });

    #api resourceful routes
    Route::apiResources([
    
        #projects api resoures controller
        'projects' => ProjectController::class, #3.index
        #project lists api resoures controller
        'project_lists' => ProjectListsController::class,
        #tasks api resoures controller
        'tasks' => TasksController::class,
        #comments api resoures controller
        'comments' => CommentsController::class,
        #replies api resoures controller
        'replies' => RepliesController::class,
        #users api resoures controller
        'users' => UsersController::class,
        
    ]);        
    
});
  