<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform-admin logos & Texts
    |--------------------------------------------------------------------------
    |
    | The logo of admin login pages. You can also set it as an image by using a
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'login-page-logo'           => '<img src="' . env('APP_URL') . '/images/logo-color.svg"     alt="' . env('APP_NAME') . '">',
    'login-page-left-image'     => '<img src="' . env('APP_URL') . '/vendor/platform/admin/images/login-bg.jpg" alt="' . env('APP_NAME') . '">',
    'footer-powered-by-text'    => '<strong>Powered by <a href="https://platform.com.sg" target="_blank">Platform</a></strong>',


    /**
     * The URIs that should be excluded from XSS processing.
     *
     * @var array
     */
    'xss-except-uris' => [
        '/admin/ext-platform-admin/code-editor*',
        '/admin/ext-platform-admin/code-editor',
        '/admin/ext-cbs-product-rating/rating_config',
        '/admin/ext-cbs-product-rating/rating_config*',
    ],

    /*
    | Enables Forgot Password Feature on Login Page
    */
    'forgot-password' => true,

    /**
     * Enables global xss protection
     *
     * Removes scripting tags from every input globally to prevent XSS attack
     */
    'enable-global-xss-protection' => true,

    /*
    |--------------------------------------------------------------------------
    | Platform-admin controllers
    |--------------------------------------------------------------------------
    |
    | Following controllers from Platform Admin can be overridden
    |
    */
    'controllers' => [
        'auth'       => App\Admin\Controllers\Platform\AuthController::class,
        'permission' => App\Admin\Controllers\Platform\PermissionController::class,
        'role'       => App\Admin\Controllers\Platform\RoleController::class,
        'user'       => App\Admin\Controllers\Platform\UserController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform-admin models
    |--------------------------------------------------------------------------
    |
    | Following models from Platform Admin can be overridden
    |
    */
    'models' => [
        'category_model'                    => App\Models\Platform\Category::class,                  // config('platform-admin.models.category_model')
        'custom_permission_model'           => App\Models\Platform\CustomPermission::class,          // config('platform-admin.models.custom_permission_model')
        'custom_permission_detail_model'    => App\Models\Platform\CustomPermissionDetail::class,    // config('platform-admin.models.custom_permission_detail_model')
        'department_model'                  => App\Models\Platform\Department::class,                // config('platform-admin.models.department_model')
        'dropdown_option_model'             => App\Models\Platform\DropdownOption::class,            // config('platform-admin.models.dropdown_option_model')
        'linkage_option_model'              => App\Models\Platform\LinkageOption::class,             // config('platform-admin.models.linkage_option_model')
        'manage_class_model'                => App\Models\Platform\ManageClass::class,               // config('platform-admin.models.manage_class_model')
        'notification_model'                => App\Models\Platform\Notification::class,              // config('platform-admin.models.notification_model')
        'oauth2_client_model'               => App\Models\Platform\OAuth2Client::class,              // config('platform-admin.models.oauth2_client_model')
        'organization_model'                => App\Models\Platform\Organization::class,              // config('platform-admin.models.organization_model')
        'product_model'                     => App\Models\Platform\Product::class,                   // config('platform-admin.models.product_model')
        'product_field_name_model'          => App\Models\Platform\ProductFieldName::class,          // config('platform-admin.models.product_field_name_model')
        'product_form_model'                => App\Models\Platform\ProductForm::class,               // config('platform-admin.models.product_form_model')
        'product_form_detail_model'         => App\Models\Platform\ProductFormDetail::class,         // config('platform-admin.models.product_form_detail_model')
        'route_list_model'                  => App\Models\Platform\RouteList::class,                 // config('platform-admin.models.route_list_model')
        'sub_category_model'                => App\Models\Platform\SubCategory::class,               // config('platform-admin.models.sub_category_model')
        'table_option_model'                => App\Models\Platform\TableOption::class,               // config('platform-admin.models.table_option_model')
        'template_model'                    => App\Models\Platform\Template::class,                  // config('platform-admin.models.template_model')
        'transaction_log_model'             => App\Models\Platform\TransactionLog::class,            // config('platform-admin.models.transaction_log_model')
        'user_subscription_model'           => App\Models\Platform\UserSubscription::class,          // config('platform-admin.models.user_subscription_model')
        'work_flow_plan_model'              => App\Models\Platform\WorkFlowPlan::class,              // config('platform-admin.models.work_flow_plan_model')
        'database_connection'               => App\Models\Platform\DatabaseConnection::class,        // config('platform-admin.models.database_connection')
        'manage_box_style'                  => App\Models\Platform\FwManageBoxStyle::class,          // config('platform-admin.models.manage_box_style')
        'manage_form_setting'               => App\Models\Platform\FwManageFormSetting::class,       // config('platform-admin.models.manage_form_setting')
        'service_model'                     => App\Models\Platform\Service::class,                   // config('platform-admin.models.service_model')
        //'application_model'                 => App\Models\Platform\FwApplication::class,             // config('platform-admin.models.application_model')
    ],

    'enable_notification'       		=> env('ENABLE_NOTIFICATION', false),
    'mqtt_server'               		=> env('MQTT_SERVER', "127.0.0.1"),
    'mqtt_port'                 		=> env('MQTT_PORT', '1883'),
    'mqtt_client_port'          		=> env('MQTT_CLIENT_PORT', 9001),
    'mqtt_client_id'            		=> env('MQTT_CLIENT_ID', "ClientID"),
    'mqtt_https_enabled'        		=> env('MQTT_HTTPS_ENABLE', 0),
    'mqtt_backend_server'       		=> env('MQTT_BACKEND_SERVER', "127.0.0.1"),
    'default_subscriber_name'   		=> env('MQTT_SUBSCRIBER_NAME', "notification-subscription"),
	'default_currency_conversion_rate'  => env('CBS_CURRENCY_CONVERSION_RATE', 1)
];
