<?php
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

$logStr =<<<LOG
[{{ method }}]|{{ url }}|{{ requestJson }}|{{ headers }}||
LOG;


$router->get('/', function () use ($router) {
    return $router->app->version();
});

//https://stackoverflow.com/questions/44711642/lumen-5-4-log-info-level-to-separate-file

$router->get('/hook/{channelId:\d{10}}', function ($channelId, \Illuminate\Http\Request $request) use ($logStr) {
    /** @var \Illuminate\Log\Logger $logger */
    $logger = app()->make(\Illuminate\Log\Logger::class);
    $info = str_replace(['{{ method }}', '{{ url }}', '{{ requestJson }}', '{{ headers }}'],
        ['GET', $request->fullUrl(), json_encode($request->toArray()), json_encode($request->headers)],
        $logStr
    );

    $logger->info($info);

    return response()->json([
        'success' => 'ok'
    ]);
});


$router->post('/hook/{channelId:\d{10}}', function ($channelId, \Illuminate\Http\Request $request) use ($logStr) {
    /** @var \Illuminate\Log\Logger $logger */
    $logger = app()->make(\Illuminate\Log\Logger::class);
    $info = str_replace(['{{ method }}', '{{ url }}', '{{ requestJson }}', '{{ headers }}'],
        ['POST', $request->fullUrl(), json_encode($request->toArray()), json_encode($request->headers)],
        $logStr
    );

    $logger->info($info);

    return response()->json([
        'success' => 'ok'
    ]);
});
