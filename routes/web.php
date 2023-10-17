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
    return $router->app->version();
});

$router->get('/index', function () {
    return '/index';
});

$router->get('/home', function () {
    return '/home';
});

$router->group(['namespace' => 'Api'], function() use ($router) {
    $router->get('/api', ['uses' => 'RestApiController@index', 'as' => 'api']);
    $router->post('/api/token', ['uses' => 'RestApiController@token', 'as' => 'apiToken']);
    $router->post('/api/select', ['uses' => 'RestApiController@select', 'as' => 'selectApi']);
    $router->post('/api/insertGetId', ['uses' => 'RestApiController@insertGetId', 'as' => 'insertGetIdApi']);
    $router->post('/api/insert', ['uses' => 'RestApiController@insertData', 'as' => 'insertApi']);
    $router->post('/api/upsert', ['uses' => 'RestApiController@upsertData', 'as' => 'upsertApi']);
    $router->post('/api/update', ['uses' => 'RestApiController@updateData', 'as' => 'updateApi']);
    $router->post('/api/increment', ['uses' => 'RestApiController@incrementFields', 'as' => 'incrementApi']);
    $router->post('/api/decrement', ['uses' => 'RestApiController@decrementFields', 'as' => 'decrementApi']);
    $router->post('/api/delete', ['uses' => 'RestApiController@deleteData', 'as' => 'deleteApi']);
});

// $router->group(['namespace' => 'Auth'], function() use ($router) {
//     $router->get('/oauth', ['uses' => 'RestApiController@index', 'as' => 'oauth']);
//     $router->post('/oauth/token', ['uses' => 'RestApiController@select', 'as' => 'authToken']);
// });
