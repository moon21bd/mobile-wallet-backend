<?php

/**
 * Created by PhpStorm.
 * User: Raqibul Hasan Moon
 * Date: 02-Dec-22
 * Time: 11:33 AM
 */

namespace App\Services;

use App\Models\AdminUser;
use App\Models\EWLimitConfig;
use App\Models\EWTrxHistory;
use App\Models\EWUserLimitConfig;
use App\Models\UserSubscription;
use App\Repositories\CoreHandler;
use App\Repositories\EWalletHandler;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class EWLimitConfigService
{
    private $defaultUserWalletId = 1;

    public function getLimitConfigs($userId, $frequencyId, $fromDate, $tillDate)
    {
        return EWUserLimitConfig::select('id', 'limit_config_id', 'user_id', 'trx_activity_type_id', 'frequency_id', 'max_trx_count', 'max_trx_amount', 'total_trx_amount', 'total_trx_count', 'status', 'created_at')
            ->where('status', 'active')
            ->with(['ewtrxactivitytype_trx_activity_type_id' => function ($query) {
                $query->select('id', 'activity_type');
            }, 'ewlimitconfig_limit_config_id' => function ($query) {
                $query->select('id', 'trx_activity_type_id', 'frequency_id', 'max_trx_count', 'max_trx_amount');
            }, 'ew_frequencies_frequency_id' => function ($query) {
                $query->select('id', 'title', 'duration', 'unit');
            }])
            ->where('user_id', $userId)
            ->where('frequency_id', $frequencyId)
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $tillDate)
            ->get();

        /*return EWLimitConfig::select('id', 'trx_activity_type_id', 'min_amount_per_trx', 'max_amount_per_trx', 'daily_max_trx_count', 'daily_max_trx_amount', 'monthly_max_trx_count', 'monthly_max_trx_amount', 'status')
            ->where('status', 'active')
            ->with(['ewtrxactivitytype_trx_activity_type_id' => function ($query) {
                $query->select('id', 'activity_type');
            }])
            ->get();*/
    }

    public function getTransactionUsage($userId = null, $trxActivityTypeId = null, $dailyOrMonthly = "daily", $getAll = false)
    {
        $usageQuery = EWTrxHistory::select('owner_id', 'trx_activity_type_id', 'trx_amount', 'trx_type', 'created_at')
            ->where(['owner_id' => $userId, 'trx_activity_type_id' => $trxActivityTypeId]);

        // $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        // $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $fromDate = '2022-11-28'; // date('Y-m-01');
        $tillDate = date('Y-m-d');

        if ($dailyOrMonthly == 'daily') {
            $query = $usageQuery->whereDate('created_at', \Carbon\Carbon::today());
            $dailyCount = $query->count();
        } else if ($dailyOrMonthly == 'monthly') {
            $query = $usageQuery->whereBetween(\DB::raw('date(created_at)'), [$fromDate, $tillDate]);
            $monthlyCount = $query->count();
        }

        if ($getAll) {
            return $query->get();
        } else {

            $getTrxUsage = $query->get();
            if ($getTrxUsage->isNotEmpty()) {

                $inOutArr = [];
                $sum = 0;
                foreach ($getTrxUsage as $key => $value) {
                    $sum += $value->trx_amount;
                    $inOutArr[$value->trx_type] = [
                        'trx_amount' => $sum,
                        "trx_activity_type_id" => $trxActivityTypeId,
                        "daily_or_monthly" => $dailyOrMonthly,
                        'created_at' => $value->created_at->toDateTimeString()
                    ];
                }
                return $inOutArr;

            } else {
                return [
                    "in" => [
                        "trx_amount" => 0,
                        "trx_activity_type_id" => $trxActivityTypeId,
                        "daily_or_monthly" => $dailyOrMonthly,
                        "created_at" => "1970-01-01 12:00:01"
                    ],
                    "out" => [
                        "trx_amount" => 0,
                        "trx_activity_type_id" => $trxActivityTypeId,
                        "daily_or_monthly" => $dailyOrMonthly,
                        "created_at" => "1970-01-01 12:00:01"
                    ]
                ];
            }
        }

    }

}
