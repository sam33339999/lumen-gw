<?php
//https://stackoverflow.com/questions/44711642/lumen-5-4-log-info-level-to-separate-file

// /bootstrap/app.php
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


$router->get('/hook/{channelId:\d{10}}', 'LineController@verify');
$router->post('/hook/{channelId:\d{10}}', 'LineController@hook');

