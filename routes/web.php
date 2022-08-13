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

$router->group(['prefix' => 'api', 'middleware' =>['auth','verified']], function () use ($router) {
    $router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);
    $router->get('users',  ['uses' => 'UserController@showAllAuthors']);
  
    $router->get('users/{id}', ['uses' => 'UserController@showOneAuthor']);
  
    #$router->post('users', ['uses' => 'UserController@create']);
  
    $router->delete('users/{id}', ['uses' => 'UserController@delete']);
    
    $router->put('users/{id}', ['uses' => 'UserController@update']);
    $router->put('passwordchange/{id}', ['uses' => 'UserController@passwordChange']);
    #$router->post('login',['uses' => 'AuthController@login']);
    $router->post('logout',['uses' => 'AuthController@logout']);
    $router->post('refresh',['uses' => 'AuthController@refresh']);
    $router->post('user-profile',['uses' => 'AuthController@me']);
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
