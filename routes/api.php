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

#api resourceful routes
Route::apiResources([
    'projects' => ProjectController::class,
    'project_lists' => ProjectListsController::class,
    'tasks' => TasksController::class,
]);

#single project.lists api route
Route::post('projects/{project}/projectLists', 'API\ProjectListsController@addListToProject')->name('projects.lists.store');