<?php


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
use App\Http\Controllers\ProjectBugherdController;
use App\Http\Controllers\Webhook\BugherdWebhookController;

Route::prefix('projects/{project}')->group(function () {
    Route::get('/bugherd/tasks', [ProjectBugherdController::class, 'index']);
    Route::post('/bugherd/tasks', [ProjectBugherdController::class, 'store']);
});

// Webhook endpoint (see §6)
Route::post('/webhooks/bugherd', [BugherdWebhookController::class, 'handle']);


ApiRoute::group(['namespace' => 'App\Http\Controllers'], function () {
    ApiRoute::get('purchased-module', ['as' => 'api.purchasedModule', 'uses' => 'HomeController@installedModule']);
});
