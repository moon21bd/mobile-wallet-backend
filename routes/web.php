<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect("/admin");
});
Route::post('webhook/create-user', [
    'as' => 'createUser',
    'uses' => 'WebhookController@createUser'
]);

Route::get("health-check", "HealthCheckController@healthCheck")->name('health.check');
Route::get('web/sso-login/{token}', 'SingleSignInController@ssoLogin')->name('sso.login')->middleware('cors');
Route::post('lp/auth/login', 'API\JDBApiController@login')->name('JDB.login');

//JDB Bank


Route::post('webhook/{fromName}', [
    'as' => 'import',
    'uses' => 'WebhookController@saveData'
]);

Route::get('pusher/get-data', function () {
    return view('welcome');
});
