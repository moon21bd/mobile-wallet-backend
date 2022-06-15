<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CBS Product Rating Controllers
    |--------------------------------------------------------------------------
    |
    | Following controllers from CBS Product Rating can be overridden
    |
    */
    'controllers' => [
        'rating_condition_name' => App\Admin\Controllers\CbsProductRating\CBSRatingConditionNameController::class,  // config('platform-cbs-product-rating.controllers.rating_condition_name')
        'rating_config'         => App\Admin\Controllers\CbsProductRating\CBSRatingConfigController::class,         // config('platform-cbs-product-rating.controllers.rating_config')
        'rating_plan'           => App\Admin\Controllers\CbsProductRating\CBSRatingPlanController::class,           // config('platform-cbs-product-rating.controllers.rating_plan')
        'rating_type'           => App\Admin\Controllers\CbsProductRating\CBSRatingTypeController::class,           // config('platform-cbs-product-rating.controllers.rating_type')
    ],

    /*
    |--------------------------------------------------------------------------
    | CBS Product Rating Models
    |--------------------------------------------------------------------------
    |
    | Following models from CBS Product Rating can be overridden
    |
    */
    'models' => [
        'rating_condition'      => App\Models\CbsProductRating\CBSRatingCondition::class,       // config('platform-cbs-product-rating.models.rating_condition')
        'rating_condition_name' => App\Models\CbsProductRating\CBSRatingConditionName::class,   // config('platform-cbs-product-rating.models.rating_condition_name')
        'rating_config'         => App\Models\CbsProductRating\CBSRatingConfig::class,          // config('platform-cbs-product-rating.models.rating_config')
        'rating_plan'           => App\Models\CbsProductRating\CBSRatingPlan::class,            // config('platform-cbs-product-rating.models.rating_plan')
        'rating_type'           => App\Models\CbsProductRating\CBSRatingType::class,            // config('platform-cbs-product-rating.models.rating_type')
    ]
];
