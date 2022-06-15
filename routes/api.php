<?php

use Illuminate\Http\Request;

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
    Route::group(['middleware' => ['guest:api', 'set.lang']], function () {
        Route::post('login', 'API\AuthController@login');
        Route::post('signup', 'API\AuthController@signup');
        Route::post('forgot-password', 'API\AuthController@postForgotPassword')->middleware('decrypt.payload');
        Route::post('reset-password', 'API\AuthController@postResetPassword')->middleware('decrypt.payload');
    });

    Route::group(['middleware' => ['auth:api', 'set.lang']], function () {
        Route::get('logout', 'API\AuthController@logout');
        Route::get('get-user', 'API\AuthController@getUser');
        Route::post('change-password', 'API\AuthController@changePassword');
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::resource('product', API\ProductController::class);
});

Route::group(["middleware" => ["auth:api", "api.permission"]], function () {
    Route::resource("admin_users", API\AdminUserController::class);
    Route::get("admin_users/{id}/subscriptions", 'API\AdminUserController@subscriptionsByUserID');
    Route::post("admin_users/{id}/subscriptions/add-balance", 'API\AdminUserController@addBalanceByUserID');
    Route::get('all-users', 'API\AdminUserController@getAll');
    Route::post('users/reset-password', 'API\AdminUserController@resetUserPasswordByAdmin');
    Route::get("admin-users-export", "API\AdminUserController@export");


    Route::resource("organizations", API\OrganizationController::class);
    Route::get('all-organizations', 'API\OrganizationController@getAll');

    Route::resource("departments", API\DepartmentController::class);
    Route::get('all-departments', 'API\DepartmentController@getAll');

    Route::resource("services", API\ServiceController::class);

    Route::resource("admin-roles", API\AdminRoleController::class);
    Route::get('all-roles', 'API\AdminRoleController@getAll');
    Route::get('custom-permission-list', 'API\AdminRoleController@getAllCustomPermission');
    Route::get("admin-roles-export", "API\AdminRoleController@export");

    Route::resource("admin-permissions", API\AdminPermissionController::class);
    Route::get('all-permissions', 'API\AdminPermissionController@getAll');
    Route::get("admin-permissions-export", "API\AdminPermissionController@export");

    Route::resource("admin-menu", API\AdminMenuController::class);
    Route::get('all-menu', 'API\AdminMenuController@getAll');
    Route::get('all-menu-for-dropdown', 'API\AdminMenuController@getAllForDropdown');
    Route::put('update-menu-order', 'API\AdminMenuController@updateMenuOrder');

    Route::get('get-dropdown-options/{id}/{requestFrom?}', 'API\GeneratorServiceController@getDropdownData');
    Route::post('get-workflow-options', 'API\GeneratorServiceController@getWorkflowData');
    Route::get('get-operation-logs', 'API\GeneratorServiceController@getOperationLogData');
    Route::any('get-custom-permission-infos', 'API\GeneratorServiceController@getCustomPermissionData');

    Route::resource("custom-permissions", API\CustomPermissionController::class);
    Route::resource("custom-permission-details", API\CustomPermissionDetailController::class);
    Route::resource("route-lists", API\RouteListController::class);
    Route::get('all-custom-permissions', 'API\CustomPermissionController@getAll');
    Route::get('all-route-lists', 'API\RouteListController@getAll');
    Route::get('get-custom-permission-detail-form-dropdown-options/{routeId}', 'API\GeneratorServiceController@getCustomPermissionDropdownOptionInfo');

});

Route::group([], function () {
    Route::get("ext-cbs-api-provider/all-applications", 'API\CBSAPIGW\CBSGWAppInfoController@getAll');
    Route::resource("ext-cbs-api-provider/gw_app_info", API\CBSAPIGW\CBSGWAppInfoController::class);
    Route::resource("ext-cbs-api-provider/gw_api_info", API\CBSAPIGW\CBSGWApiInfoController::class);
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'ext-report-builder', 'namespace' => 'API\ReportBuilder'], function () {
    Route::get('db-connections', 'QueryBuilderController@getDBConnections');
    Route::get('get-tables-by-connection', 'QueryBuilderController@getTablesByConnection');
    Route::get('get-queries-by-connection', 'QueryBuilderController@getQueriesByConnection');
    Route::get('get-columns-by-query', 'QueryBuilderController@getColumnsByQuery');
    Route::get('get-table-columns', 'QueryBuilderController@getColumns');
    Route::post('store-report-query/{id?}', 'QueryBuilderController@storeReportQuery');
    Route::get('query-reports/{id}', 'QueryBuilderController@getQueryReportById');
    Route::get('query-reports', 'QueryBuilderController@getQueryReports');
    Route::delete('query-reports/{id}', 'QueryBuilderController@removeQueryReportById');
});

Route::post('sso-user-validation', 'SingleSignInController@ssoValidation')->middleware('cors');

//CBS
Route::group(['middleware' => 'auth:api', 'prefix' => 'cbs', 'namespace' => 'API\CBS'], function () {
    //accounting
    Route::resource("accounting/general-ledger-account", GeneralLedgerAccountController::class);
    Route::get("accounting/all-general-ledger-account", 'GeneralLedgerAccountController@getAll');
    Route::resource("accounting/account", AccountController::class);
    Route::get("accounting/all-account", 'AccountController@getAll');
    Route::resource("accounting/accounting-transaction", AccountingTransactionController::class);

    //commission
    Route::resource("commission/commission-plan", CommissionPlanController::class);
    Route::get("commission/all-commission-plan", "CommissionPlanController@getAll");
    Route::resource("commission/commission-condition-name", CommissionConditionNameController::class);
    Route::get("commission/all-condition-name", 'CommissionConditionNameController@getAll');
    Route::resource("commission/commission-config", CommissionConfigController::class);
    Route::get("commission/all-commission-config", 'CommissionConfigController@getAll');
    Route::resource("commission/commission-deferred", CommissionDeferredController::class);
    Route::resource("commission/commission-log", CommissionLogController::class);
    Route::resource("commission/sales-log", SalesLogController::class);

    //product
    Route::resource("product/product-category", ProductCategoryController::class);
    Route::get("product/all-product-category", "ProductCategoryController@getAll");
    Route::resource("product/products", ProductController::class);
    Route::get("product/all-product", "ProductController@getAll");

    //rating
    Route::resource("product-rating/rating-type", RatingTypeController::class);
    Route::get("product-rating/all-rating-type", 'RatingTypeController@getAll');
    Route::resource("product-rating/rating-condition-name", RatingConditionNameController::class);
    Route::get("product-rating/all-rating-condition-name", 'RatingConditionNameController@getAll');
    Route::resource("product-rating/rating-plan", RatingPlanController::class);
    Route::get("product-rating/all-rating-plan", 'RatingPlanController@getAll');
    Route::resource("product-rating/rating-config", RatingConfigController::class);

    Route::resource("user-management/user-payment", UserPaymentMethodController::class);
});

// E-Wallet-API
Route::group(['middleware' => ['auth:api', 'check.status', 'set.lang'], 'namespace' => 'API\EWallet'], function () {
    Route::post('do-transaction', 'TransactionController@doTransaction')->middleware('decrypt.payload');
    Route::post('check-transaction', 'TransactionController@trxCheck')->middleware('decrypt.payload');
    Route::post('get-rate-check', 'TransactionController@getRateCheck');
    Route::get('get-user-by-username', 'AuthController@getSpecificUserInfoById')->middleware('decrypt.payload');
    Route::post('change-password', 'AuthController@changePassword')->middleware('decrypt.payload');
    Route::post('get-transaction-history', 'TransactionController@getTransactionHistory')->middleware('decrypt.payload');
    Route::post('get-transaction-limits', 'TransactionController@getTransactionLimits');
});

Route::group(['middleware' => ['auth:api', 'set.lang'], 'namespace' => 'API\EWallet'], function () {
    Route::post('get-user-status', 'AuthController@getUserStatus');
    Route::put('update-user-info', 'AuthController@updateUserInfo')->middleware('decrypt.payload');
    Route::post('get-user-balance', 'TransactionController@getUserBalance');
    Route::post('trx-otp-request', 'OTPController@trxOtpRequest')->middleware('decrypt.payload');
    Route::post('get-user-info', 'AuthController@getUserInfo');
});

// without authentication check
Route::group(['middleware' => ['set.lang','decrypt.payload'], 'namespace' => 'API\EWallet'], function () {
    Route::post('otp-request', 'OTPController@otpRequest');

    Route::post('otp-verify', 'OTPController@otpVerify');
    Route::post('user-registration', 'AuthController@signup');
    Route::get('get-security-type', 'AuthController@getSecurityType');
    Route::get('get-city-country', 'AuthController@getCityAndCountry');
    Route::get('get-country', 'AuthController@getCountry');
    Route::get('get-city', 'AuthController@getCity');
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-limit-configs-export", "API\EWLimitConfigController@export");
    Route::delete("ew_limit_configs/delete-all", "API\EWLimitConfigController@deleteAll")->name("eWLimitConfigDeleteAllControl");
    Route::resource("ew_limit_configs", API\EWLimitConfigController::class);
    Route::get("ew-limit-configs-details/{id}", "API\EWLimitConfigController@getDetails");
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-details-export", "API\EWUserDetailController@export");
    Route::get("get-countries", "API\EWUserDetailController@getCountries");
    Route::get("get-cities", "API\EWUserDetailController@getCities");
    Route::delete("ew_user_details/delete-all", "API\EWUserDetailController@deleteAll")->name("eWUserDetailDeleteAllControl");
    Route::resource("ew_user_details", API\EWUserDetailController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-pending-details-export", "API\EWUserPendingDetailController@export");
    Route::delete("ew_user_pending_details/delete-all", "API\EWUserPendingDetailController@deleteAll")->name("eWUserDetailPendingDeleteAllControl");
    Route::resource("ew_user_pending_details", API\EWUserPendingDetailController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-limit-config-logs-export", "API\EWLimitConfigLogController@export");
    Route::delete("ew_limit_config_logs/delete-all", "API\EWLimitConfigLogController@deleteAll")->name("eWLimitConfigLogDeleteAllControl");
    Route::resource("ew_limit_config_logs", API\EWLimitConfigLogController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-otp-histories-export", "API\EWOTPHistoryController@export");
    Route::delete("ew_otp_histories/delete-all", "API\EWOTPHistoryController@deleteAll")->name("eWOTPHistoryDeleteAllControl");
    Route::resource("ew_otp_histories", API\EWOTPHistoryController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-otp-usage-logs-export", "API\EWOTPUsageLogController@export");
    Route::delete("ew_otp_usage_logs/delete-all", "API\EWOTPUsageLogController@deleteAll")->name("eWOTPUsageLogDeleteAllControl");
    Route::resource("ew_otp_usage_logs", API\EWOTPUsageLogController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("countries-export", "API\CountryController@export");
    Route::delete("countries/delete-all", "API\CountryController@deleteAll")->name("countryDeleteAllControl");
    Route::resource("countries", API\CountryController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("cities-export", "API\CityController@export");
    Route::delete("cities/delete-all", "API\CityController@deleteAll")->name("cityDeleteAllControl");
    Route::resource("cities", API\CityController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-countries-export", "API\EWCountryController@export");
    Route::delete("ew_countries/delete-all", "API\EWCountryController@deleteAll")->name("eWCountryDeleteAllControl");
    Route::resource("ew_countries", API\EWCountryController::class);
});
Route::group(["middleware" => ["auth:api","decrypt.payload"]], function () {
    Route::get("ew-cities-export", "API\EWCityController@export");
    Route::delete("ew_cities/delete-all", "API\EWCityController@deleteAll")->name("eWCityDeleteAllControl");
    Route::resource("ew_cities", API\EWCityController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-trx-histories-export", "API\EWTrxHistoryController@export");
    Route::delete("ew_trx_histories/delete-all", "API\EWTrxHistoryController@deleteAll")->name("eWTrxHistoryDeleteAllControl");
    Route::resource("ew_trx_histories", API\EWTrxHistoryController::class);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-trx-activity-types-export", "API\EWTrxActivityTypeController@export");
    Route::get("get-trx-activity-types", "API\EWTrxActivityTypeController@getActivityTypes");
    Route::delete("ew_trx_activity_types/delete-all", "API\EWTrxActivityTypeController@deleteAll")->name("eWTrxActivityTypeDeleteAllControl");
    Route::resource("ew_trx_activity_types", API\EWTrxActivityTypeController::class);
    Route::get("ew_trx_activity_types_details/{id}", "API\EWTrxActivityTypeController@getDetails");
    Route::get("getTrxActivityTypeLogs/{id}", "API\EWTrxActivityTypeController@getTrxActivityTypeLogs");

});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-trx-activity-types-export", "API\EWTrxActivityTypeController@export");
    Route::delete("ew_trx_activity_types/delete-all", "API\EWTrxActivityTypeController@deleteAll")->name("eWTrxActivityTypeDeleteAllControl");
    Route::resource("ew_trx_activity_types", API\EWTrxActivityTypeController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-limit-configs-export", "API\EWUserLimitConfigController@export");
    Route::delete("ew_user_limit_configs/delete-all", "API\EWUserLimitConfigController@deleteAll")->name("eWUserLimitConfigDeleteAllControl");
    Route::resource("ew_user_limit_configs", API\EWUserLimitConfigController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-limit-configs-export", "API\EWUserLimitConfigController@export");
    Route::delete("ew_user_limit_configs/delete-all", "API\EWUserLimitConfigController@deleteAll")->name("eWUserLimitConfigDeleteAllControl");
    Route::resource("ew_user_limit_configs", API\EWUserLimitConfigController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-config-export", "API\EWConfigController@export");
    Route::delete("ew_config/delete-all", "API\EWConfigController@deleteAll")->name("eWConfigDeleteAllControl");

    Route::get("get-configuration", "API\EWConfigController@getConfiguration");
    Route::put("update-configuration", "API\EWConfigController@updateConfiguration");
    Route::get("get-configuration-logs/{id}", "API\EWConfigController@getConfigurationLogs");
});

Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-config-logs-export", "API\EWConfigLogController@export");
    Route::delete("ew_config_logs/delete-all", "API\EWConfigLogController@deleteAll")->name("eWConfigLogDeleteAllControl");
    Route::resource("ew_config_logs", API\EWConfigLogController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-config-export", "API\EWUserConfigController@export");
    Route::delete("ew_user_config/delete-all", "API\EWUserConfigController@deleteAll")->name("eWUserConfigDeleteAllControl");
    Route::resource("ew_user_config", API\EWUserConfigController::class);
    Route::get("get-user-config/{id}", "API\EWUserConfigController@getConfiguration");
    Route::put("update-user-config", "API\EWUserConfigController@updateUserConfigurations");
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-config-logs-export", "API\EWUserConfigLogController@export");
    Route::delete("ew_user_config_logs/delete-all", "API\EWUserConfigLogController@deleteAll")->name("eWUserConfigLogDeleteAllControl");
    Route::resource("ew_user_config_logs", API\EWUserConfigLogController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-trx-activity-type-logs-export", "API\EWTrxActivityTypeLogController@export");
    Route::delete("ew_trx_activity_type_logs/delete-all", "API\EWTrxActivityTypeLogController@deleteAll")->name("eWTrxActivityTypeLogDeleteAllControl");
    Route::resource("ew_trx_activity_type_logs", API\EWTrxActivityTypeLogController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-activity-limits-export", "API\EWUserActivityLimitController@export");
    Route::delete("ew_user_activity_limits/delete-all", "API\EWUserActivityLimitController@deleteAll")->name("eWUserActivityLimitDeleteAllControl");
    Route::resource("ew_user_activity_limits", API\EWUserActivityLimitController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-activity-limit-logs-export", "API\EWUserActivityLimitLogController@export");
    Route::delete("ew_user_activity_limit_logs/delete-all", "API\EWUserActivityLimitLogController@deleteAll")->name("eWUserActivityLimitLogDeleteAllControl");
    Route::resource("ew_user_activity_limit_logs", API\EWUserActivityLimitLogController::class);
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-user-limit-config-logs-export", "API\EWUserLimitConfigLogController@export");
    Route::delete("ew_user_limit_config_logs/delete-all", "API\EWUserLimitConfigLogController@deleteAll")->name("eWUserLimitConfigLogDeleteAllControl");
    Route::resource("ew_user_limit_config_logs", API\EWUserLimitConfigLogController::class);
    Route::get("getLimitConfigLogs/{id}", "API\EWLimitConfigLogController@getLimitConfigLogs");
});
Route::group(["middleware" => "auth:api"], function () {
    Route::get("ew-frequencies-export", "API\EWFrequencyController@export");
    Route::delete("ew_frequencies/delete-all", "API\EWFrequencyController@deleteAll")->name("eWFrequencyDeleteAllControl");
    Route::resource("ew_frequencies", API\EWFrequencyController::class);
    Route::get("get-frequencies", "API\EWFrequencyController@getFrequencies");
});

Route::post("get-decrypted-data", "API\EncryptDecryptController@decryptPayload");
Route::post("get-encrypted-data", "API\EncryptDecryptController@encryptPayload");

//api for JDB Bank
Route::group(["middleware" => "auth:api"], function () {
    Route::post("transaction/wallet/wallet-detail", "API\JDBApiController@walletDetails");
    Route::post("transaction/wallet/cash-in", "API\JDBApiController@walletCashIn");
    Route::post("transaction/wallet/cash-out", "API\JDBApiController@walletCashOut");
    Route::post("transaction/wallet/confirm/cash-out", "API\JDBApiController@walletConfirmCashOut");
});

Route::get("status","API\StatusController@getStatus");

//api for transfer to bank
Route::group(["middleware" => ["auth:api","decrypt.payload"]], function () {
    Route::post("bank/transfer-request", "API\EWallet\BankApiController@transferRequest");
    Route::post("bank/transfer-resend-otp", "API\EWallet\BankApiController@transferResendOtp");
    Route::post("bank/transfer-confirm", "API\EWallet\BankApiController@transferConfirm");
});

//user limit config copy
Route::post("webhook/sync-limit-config", "WebhookController@syncLimitConfig");
