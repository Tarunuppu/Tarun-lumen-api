<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    #return $router->app->version();
    dd(DB::getPDO());
});


$router->post('api/users',['uses' => 'UserController@create']);
$router->post('api/login',['uses' => 'AuthController@login']);
$router->post('api/email/verify', ['as' => 'email.verify', 'uses' => 'AuthController@emailVerify']);
$router->post('api/forgetpassword',['uses' => 'AuthController@forgetPassword']);
$router->post('api/forgetpassword-emailverification',['as' => 'forgetpassword.emailverify','uses' => 'AuthController@forgetPassword_EmailVerification']);
$router->post('api/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);
$router->post('api/logout',['uses' => 'AuthController@logout']);
$router->group(['prefix' => 'api', 'middleware' =>['auth','verified']], function () use ($router) {
    $router->get('get-users',  ['uses' => 'UserController@showAllAuthors']);
  
    $router->get('users/{id}', ['uses' => 'UserController@showOneAuthor']);
  
    #$router->post('users', ['uses' => 'UserController@create']);
  
    $router->delete('delete', ['uses' => 'UserController@delete']);
    $router->delete('deleteuser',['uses' => 'UserController@deleteuser']);
    $router->put('update', ['uses' => 'UserController@update']);
    $router->put('passwordchange', ['uses' => 'UserController@passwordChange']);
    #$router->post('login',['uses' => 'AuthController@login']);

    $router->post('refresh',['uses' => 'AuthController@refresh']);
    $router->post('user-profile',['uses' => 'AuthController@me']);
    $router->get('getpartofusers', ['uses' => 'UserController@getpartofusers']);
    $router->get('sizeofdatabase', ['uses' => 'UserController@sizeofdatabase']);
    #$router->post('sendpasswordthroughmail',['uses' => 'AuthController@sendpasswordthroughmail']);
  });

  $router->group(['prefix' => 'task','middleware' =>['auth','verified']],function () use ($router){
        #$router->get('self-tasks',['uses' => 'TaskController@selfTasks']);
        #$router->get('created-tasks',['uses' => 'TaskController@createdTasks']);
        $router->get('gettasks-multiplefilters',['uses' => 'TaskController@getTasksMultipleFilters']);
        $router->get('getalltasks-statusbased',['uses' => 'TaskController@getAllTasksStatusBased']);
        $router->get('getalltasksforpie',['uses' => 'TaskController@getAllTasksForPie']);
        $router->get('getassignedtome',['uses' => 'TaskController@getAssignedToMe']);
        $router->get('getcreatedbyme',['uses' => 'TaskController@getCreatedByMe']);
        $router->get('onetask',['uses' => 'TaskController@oneTask']);
        $router->get('all-tasks',['uses' => 'TaskController@allTasks']);
        $router->get('get-tasks',['uses' => 'TaskController@getTasks']);
        $router->post('create-tasks',['uses' => 'TaskController@createTasks']);
        $router->delete('delete-tasks',['uses' => 'TaskController@deleteTasks']);
        $router->delete('delete-multiple-tasks',['uses' => 'TaskController@deleteMultipleTasks']);
        $router->put('update-tasks',['uses' => 'TaskController@updateTasks']);
        $router->get('getspecificcolumns',['uses' => 'TaskController@getSpecificColumns']);

        $router->get('getnotification',['uses' => 'NotificationController@getNotification']);
        $router->delete('deletenotification',['uses' => 'NotificationController@deleteNotification']);
        $router->delete('clearnotification',['uses' => 'NotificationController@clearNotification']);

  });



 /* Route::group([

    'prefix' => 'api'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('user-profile', 'AuthController@me');

});
*/
