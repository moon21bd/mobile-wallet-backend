<?php

use Illuminate\Routing\Router;
use Platform\Admin\Facades\Admin;

Admin::routes();

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get("notification-example", "NotificationExampleController@notificationExample")->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_user_details", EWUserDetailChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_user_pending_details", EWUserPendingDetailChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_limit_configs", EWLimitConfigChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_user_limit_configs", EWUserLimitConfigChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_user_details", EWUserDetailChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_trx_activity_types", EWTrxActivityTypeChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_trx_histories", EWTrxHistoryChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_limit_config_logs", EWLimitConfigLogChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_otp_histories", EWOTPHistoryChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_otp_usage_logs", EWOTPUsageLogChildController::class)->middleware('admin.custom_permission');
});


Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_countries", EWCountryChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_cities", EWCityChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->resource("ew_user_registration_steps", EWUserRegistrationStepChildController::class)->middleware('admin.custom_permission');
});

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
], function (Router $router) {
    $router->get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('clear-compiled');
        Artisan::call('optimize:clear');
        return "Cache is cleared & Also clear browser cache for Home page new data";
    });
});
