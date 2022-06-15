<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Builder Controllers
    |--------------------------------------------------------------------------
    |
    | Following controllers from Report Builder can be overridden
    |
    */
    'controllers' => [
        'api_info'          => App\Admin\Controllers\CbsApiProvider\CBSGWApiInfoController::class,       // config('platform-cbs-api-provider.controllers.api_info')
        'app_info'          => App\Admin\Controllers\CbsApiProvider\CBSGWAppInfoController::class,       // config('platform-cbs-api-provider.controllers.app_info')
        'gw_organization'   => App\Admin\Controllers\CbsApiProvider\CBSGWOrganizationController::class,  // config('platform-cbs-api-provider.controllers.gw_organization')
        'gw_role'           => App\Admin\Controllers\CbsApiProvider\CBSGWRoleController::class,          // config('platform-cbs-api-provider.controllers.gw_role')
        'gw_user'           => App\Admin\Controllers\CbsApiProvider\CBSGWUserController::class,          // config('platform-cbs-api-provider.controllers.gw_user')
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Builder Models
    |--------------------------------------------------------------------------
    |
    | Following models from Report Builder can be overridden
    |
    */
    'models' => [
        'api_info'          => App\Models\CbsApiProvider\CBSGWApiInfo::class,        // config('platform-cbs-api-provider.models.api_info')
        'api_parameter'     => App\Models\CbsApiProvider\CBSGWApiParameter::class,   // config('platform-cbs-api-provider.models.api_parameter')
        'app_info'          => App\Models\CbsApiProvider\CBSGWAppInfo::class,        // config('platform-cbs-api-provider.models.app_info')
        'gw_organization'   => App\Models\CbsApiProvider\CBSGWOrganization::class,   // config('platform-cbs-api-provider.models.gw_organization')
        'gw_role'           => App\Models\CbsApiProvider\CBSGWRole::class,           // config('platform-cbs-api-provider.models.gw_role')
        'gw_user'           => App\Models\CbsApiProvider\CBSGWUser::class,           // config('platform-cbs-api-provider.models.gw_user')
    ]
];
