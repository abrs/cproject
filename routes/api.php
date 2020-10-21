<?php

use Illuminate\Support\Facades\Route;
use API\ProjectListsController;
use Illuminate\Http\Request;
use API\ProjectController;
use API\TasksController;
use API\CommentsController;
use API\RepliesController;
use API\UsersController;
use API\AuthController;
use App\Image;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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

    #Roles and permissions
    Route::group(['prefix' => 'roles-permissions', 'middleware' => ['role:manager']], function () {
        #3-1.1 - get all permissions to assign some of them or all to a role
        Route::get('/permissions', function() {return response()->json(['permissions' => Permission::all()]);});
        #3-1.2 - get all roles to assign some of them or all permissions
        Route::get('/roles', function() {return response()->json(['roles' => Role::all()]);});
        #1- add permission
        Route::post('/permissions/add-permission', 'API\AuthController@addPermisssion');
        #2- add role
        Route::post('/roles/add-role', 'API\AuthController@addRole');
        #3-2 - sync role with some permissions => detach all role's permissions and attach the new ones.
        Route::post('/roles/{role}/add-permissions', 'API\AuthController@assignPermissionToRole');
        #4 - sync user with role| roles
        Route::post('/users/{user}/assign-roles', 'API\AuthController@assignRoleToUser');
    });

    #special tasks
    Route::get('filters', 'API\TasksController@customFilter');

    Route::group(['prefix' => 'projects'], function() {
    
        #assign task to a member route
        Route::post('/tasks/assign-to-me', 'API\TasksController@assignTaskToMe'); #2-1
        
        #assign task to a member route
        Route::post('/tasks/assign-member', 'API\TasksController@assignTaskToMember'); #2-2
        
        #get shared projects
        Route::get('/all-shared-projects', 'API\ProjectController@getSharedProjects'); #4
        
        #get project tasks iDs route
        Route::get('/my-tasks', 'API\TasksController@getMyTasks'); #5
        
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
  