<?php

use API\ProjectController;
use API\ProjectListsController;
use API\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::post('login', 'API\AuthController@login');
    #register a user into the system 
    Route::post('signup', 'API\AuthController@signup');

    Route::group(['middleware' => 'auth:api'], function() {
    
        #logout route
        Route::get('logout', 'API\AuthController@logout');
        #user route
        Route::get('user', 'API\AuthController@user');
    });

});


Route::group(['middleware' => 'auth:api'], function() {

    #api resourceful routes
    Route::apiResources([
    
        #projects api resoures controller
        'projects' => ProjectController::class,
        #project lists api resoures controller
        'project_lists' => ProjectListsController::class,
        #tasks api resoures controller
        'tasks' => TasksController::class,
    ]);
    
    
    Route::prefix('projects')->group(function() {
    
        #assign task to a member route
        Route::post('/tasks/assign-members', 'API\TasksController@assignTaskToMember');

        #get project members route
        Route::get('/{project}/members', 'API\ProjectController@getProjectMembers');
        
        #get project tasks route
        Route::get('/{project}/tasks', 'API\ProjectController@getProjectTasks');

        #add member to a project route
        Route::post('new-member-{project}', 'API\ProjectController@addMember');
        
        #single project.lists api route
        Route::post('{project}/projectLists', 'API\ProjectListsController@addListToProject');
    });
});
  