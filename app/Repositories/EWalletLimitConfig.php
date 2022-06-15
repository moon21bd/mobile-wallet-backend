<?php

namespace App\Repositories;


use App\Models\EWLimitConfig;
use App\Models\EWTrxActivityType;
use App\Models\EWUserActivityLimit;
use App\Models\EWUserConfig;
use App\Models\EWUserLimitConfig;
use App\Models\UserSubscription;
use Encore\Admin\Config\ConfigModel;
use App\Models\EWFrequency;
use App\Repositories\CoreHandler;
use Illuminate\Support\Facades\Log;

class EWalletLimitConfig
{
    public static $defaultUserWalletId = 1;
    public static $logEnable           = true;

    public static function copyConfig($user_id)
    {
        //get the global config
        $global_config = ConfigModel::where('name', 'max_balance')
                                    ->first()
        ;
        if ($global_config) {
            //copy to user table
            $config_data = ['config_id' => $global_config->id, 'user_id' => $user_id, 'name' => $global_config->name, 'value' => $global_config->value,];
            $user_config = new EWUserConfig();
            $user_config->fill($config_data);
            $user_config->save();
        }

        //activity wise config
        $activity_configs = EWTrxActivityType::all();
        if ($activity_configs) {
            $user_activity_data = [];
            //copy to user
            foreach ($activity_configs as $activity_config) {
                $user_activity_data[] = [
                    'activity_type_id'   => $activity_config->id,
                    'user_id'            => $user_id,
                    'per_trx_min_amount' => $activity_config->per_trx_min_amount,
                    'per_trx_max_amount' => $activity_config->per_trx_max_amount,
                    'status'             => $activity_config->status,
                ];
            }
            if ($user_activity_data) {
                EWUserActivityLimit::insert($user_activity_data);
            }
        }

        //limit config
        $limit_configs = EWLimitConfig::all();
        if ($limit_configs) {
            $user_limit_config_data = [];
            //copy to user  ew_user_limit_configs
            foreach ($limit_configs as $limit_config) {
                $user_limit_config_data[] = [
                    'limit_config_id'      => $limit_config->id,
                    'user_id'              => $user_id,
                    'trx_activity_type_id' => $limit_config->trx_activity_type_id,
                    'frequency_id'         => $limit_config->frequency_id,
                    'max_trx_count'        => $limit_config->max_trx_count,
                    'max_trx_amount'       => $limit_config->max_trx_amount,
                    'status'               => $limit_config->status,
                ];
            }
            if ($user_limit_config_data) {
                EWUserLimitConfig::insert($user_limit_config_data);
            }
        }
        return true;
    }

    public static function checkTrxLimit($user_id, $activity_type_id, $amount, $action, $language)
    {   //request
        $request_params = [
            'user_id'          => $user_id,
            'activity_type_id' => $activity_type_id,
            'amount'           => $amount,
            'action'           => $action,
            'language'         => $language,
        ];

        $action_key = strtoupper($action);
        $response   = [
            'status'            => false,
            'message_key'       => $action_key.'_INSUFFICIENT_LIMIT',
            'message'           => self::validationMsg($language, $action_key.'_INSUFFICIENT_LIMIT'),
            'message_with_lang' => self::validationMsg($language, $action_key.'_INSUFFICIENT_LIMIT', true),
        ];
        //if action received then check the balance
        if ($action == 'received') {
            //get user current balance
            $current_balance = self::getUserBalance($user_id);
            $new_balance     = $current_balance + $amount;
            $user_config     = EWUserConfig::where('user_id', $user_id)
                                           ->where('name', 'max_balance')
                                           ->first()
            ;
            if ($user_config) {
                if ($new_balance > $user_config->value) {
                    $response['status']            = false;
                    $response['message_key']       = $action_key.'_MAX_WALLET_EXCEEDED';
                    $response['message']           = self::validationMsg($language, $action_key.'_MAX_WALLET_EXCEEDED');
                    $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MAX_WALLET_EXCEEDED', true);
                    if (self::$logEnable) {
                        Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                    }
                    return $response;
                }
            } else {
                $global_config = ConfigModel::where('name', 'max_balance')
                                            ->first()
                ;
                if ($global_config) {
                    if ($new_balance > $global_config->value) {
                        $response['status']            = false;
                        $response['message_key']       = $action_key.'_MAX_WALLET_EXCEEDED';
                        $response['message']           = self::validationMsg($language, $action_key.'_MAX_WALLET_EXCEEDED');
                        $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MAX_WALLET_EXCEEDED', true);
                        if (self::$logEnable) {
                            Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                        }
                        return $response;
                    }
                }
            }
        }

        //check activity type min and max amount
        $user_activity_limit = EWUserActivityLimit::where('activity_type_id', $activity_type_id)
                                                  ->where('user_id', $user_id)
                                                  ->first()
        ;
        if ($user_activity_limit) {
            //check min
            if ($user_activity_limit->per_trx_min_amount > $amount) {
                $response['status']            = false;
                $response['message_key']       = $action_key.'_MIN_AMOUNT_REQUIRED';
                $response['message']           = self::validationMsg($language, $action_key.'_MIN_AMOUNT_REQUIRED');
                $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MIN_AMOUNT_REQUIRED', true);
                if (self::$logEnable) {
                    Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                }
                return $response;
            }

            //check max
            if ($user_activity_limit->per_trx_max_amount < $amount) {
                $response['status']            = false;
                $response['message_key']       = $action_key.'_MAX_AMOUNT_REQUIRED';
                $response['message']           = self::validationMsg($language, $action_key.'_MAX_AMOUNT_REQUIRED');
                $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MAX_AMOUNT_REQUIRED', true);
                if (self::$logEnable) {
                    Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                }
                return $response;
            }
        } else {
            $activity_limit = EWTrxActivityType::where('id', $activity_type_id)
                                               ->where('status', 'active')
                                               ->first()
            ;
            if ($activity_limit) {
                //check min
                if ($activity_limit->per_trx_min_amount > $amount) {
                    $response['status']            = false;
                    $response['message_key']       = $action_key.'_MIN_AMOUNT_REQUIRED';
                    $response['message']           = self::validationMsg($language, $action_key.'_MIN_AMOUNT_REQUIRED');
                    $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MIN_AMOUNT_REQUIRED', true);
                    if (self::$logEnable) {
                        Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response# Global-'.json_encode($response));
                    }
                    return $response;
                }

                //check max
                if ($activity_limit->per_trx_max_amount < $amount) {
                    $response['status']            = false;
                    $response['message_key']       = $action_key.'_MAX_AMOUNT_REQUIRED';
                    $response['message']           = self::validationMsg($language, $action_key.'_MAX_AMOUNT_REQUIRED');
                    $response['message_with_lang'] = self::validationMsg($language, $action_key.'_MAX_AMOUNT_REQUIRED', true);
                    if (self::$logEnable) {
                        Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                    }
                    return $response;
                }
            }
        }

        //get the frequency
        $all_frequencies = EWFrequency::all();
        foreach ($all_frequencies as $frequency) {
            $user_limit_config = EWUserLimitConfig::where('user_id', $user_id)
                                                  ->where('trx_activity_type_id', $activity_type_id)
                                                  ->where('frequency_id', $frequency->id)
                                                  ->where('status', 'active')
                                                  ->first()
            ;
            if ($user_limit_config) {
                $frequency_title = strtoupper($frequency->title); // DAILY, MONTHLY

                //check trx count
                if ($user_limit_config->max_trx_count < ($user_limit_config->total_trx_count + 1)) {
                    $response['status'] = false;

                    if ($frequency_title === 'DAILY') {
                        $frequency_title_as_key = $action_key.'_X_TRX_COUNT_EXCEEDED_DAILY';
                    } elseif ($frequency_title === 'MONTHLY') {
                        $frequency_title_as_key = $action_key.'_X_TRX_COUNT_EXCEEDED_MONTHLY';
                    }

                    $response['message_key']       = $frequency_title_as_key;
                    $response['message']           = self::validationMsg($language, $frequency_title_as_key);
                    $response['message_with_lang'] = self::validationMsg($language, $frequency_title_as_key, true);
                    if (self::$logEnable) {
                        Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                    }
                    return $response;
                }
                //check trx amount
                if ($user_limit_config->max_trx_amount < ($user_limit_config->total_trx_amount + $amount)) {
                    $response['status'] = false;

                    if ($frequency_title === 'DAILY') {
                        $frequency_title_as_key = $action_key.'_TOTAL_TRX_AMOUNT_EXCEEDED_DAILY';
                    } else {
                        if ($frequency_title === 'MONTHLY') {
                            $frequency_title_as_key = $action_key.'_TOTAL_TRX_AMOUNT_EXCEEDED_MONTHLY';
                        }
                    }
                    $response['message_key']       = $frequency_title_as_key;
                    $response['message']           = self::validationMsg($language, $frequency_title_as_key);
                    $response['message_with_lang'] = self::validationMsg($language, $frequency_title_as_key, true);
                    if (self::$logEnable) {
                        Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
                    }
                    return $response;
                }
            } else {
                $global_limit_config = EWLimitConfig::where('trx_activity_type_id', $activity_type_id)
                                                    ->where('frequency_id', $frequency->id)
                                                    ->where('status', 'active')
                                                    ->first()
                ;
                if ($global_limit_config) {
                    //need to discussion
                }
            }
        }

        $response['status']            = true;
        $response['message_key']       = $action_key.'_ALLOWED_TRANSACTION';
        $response['message']           = self::validationMsg($language, $action_key.'_ALLOWED_TRANSACTION');
        $response['message_with_lang'] = self::validationMsg($language, $action_key.'_ALLOWED_TRANSACTION', true);
        if (self::$logEnable) {
            Log::info('checkTrxLimit--- Request#'.json_encode($request_params).' - Response#'.json_encode($response));
        }
        return $response;
    }

    public static function validationMsg($language = "en", $messageKey = '', $asArray = false)
    {
        if (self::$logEnable) {
            Log::info('validationMsg-- messageKey#'.$messageKey);
        }
        if ($asArray) {
            return ["en" => config("validation-messages.en")[$messageKey], "la" => config("validation-messages.la")[$messageKey]];
        } else {
            return config("validation-messages.$language")[$messageKey];
        }
    }

    private static function getUserBalance($userId)
    {
        // update core balance
        $subscriptionDetail = UserSubscription::where(['user_id' => $userId, 'subscription_category_id' => self::$defaultUserWalletId])
                                              ->first()
        ;

        if ($subscriptionDetail) {
            return (new CoreHandler())->getBalance($subscriptionDetail->core_ref_id ?? uniqid());
        } else {
            return 0;
        }
    }
}
