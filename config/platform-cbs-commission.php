<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CBS Commission Controllers
    |--------------------------------------------------------------------------
    |
    | Following controllers from CBS Commission can be overridden
    |
    */
    'controllers' => [
        'commission_condition_name' => App\Admin\Controllers\CbsCommission\CBSCommissionConditionNameController::class, // config('platform-cbs-commission.controllers.commission_condition_name')
        'commission_config'         => App\Admin\Controllers\CbsCommission\CBSCommissionConfigController::class,        // config('platform-cbs-commission.controllers.commission_config')
        'commission_deferred'       => App\Admin\Controllers\CbsCommission\CBSCommissionDeferredController::class,      // config('platform-cbs-commission.controllers.commission_deferred')
        'commission_log'            => App\Admin\Controllers\CbsCommission\CBSCommissionLogController::class,           // config('platform-cbs-commission.controllers.commission_log')
        'commission_plan'           => App\Admin\Controllers\CbsCommission\CBSCommissionPlanController::class,          // config('platform-cbs-commission.controllers.commission_plan')
        'sales_log'                 => App\Admin\Controllers\CbsCommission\CBSSalesLogController::class,                // config('platform-cbs-commission.controllers.sales_log')
    ],

    /*
    |--------------------------------------------------------------------------
    | CBS Commission Models
    |--------------------------------------------------------------------------
    |
    | Following models from CBS Commission can be overridden
    |
    */
    'models' => [
        'commission_condition'      => App\Models\CbsCommission\CBSCommissionCondition::class,      // config('platform-cbs-commission.models.commission_condition')
        'commission_condition_name' => App\Models\CbsCommission\CBSCommissionConditionName::class,  // config('platform-cbs-commission.models.commission_condition_name')
        'commission_config'         => App\Models\CbsCommission\CBSCommissionConfig::class,         // config('platform-cbs-commission.models.commission_config')
        'commission_deferred'       => App\Models\CbsCommission\CBSCommissionDeferred::class,       // config('platform-cbs-commission.models.commission_deferred')
        'commission_log'            => App\Models\CbsCommission\CBSCommissionLog::class,            // config('platform-cbs-commission.models.commission_log')
        'commission_plan'           => App\Models\CbsCommission\CBSCommissionPlan::class,           // config('platform-cbs-commission.models.commission_plan')
        'commission_split'          => App\Models\CbsCommission\CBSCommissionSplit::class,          // config('platform-cbs-commission.models.commission_split')
        'sales_log'                 => App\Models\CbsCommission\CBSSalesLog::class,                 // config('platform-cbs-commission.models.sales_log')
    ]
];
