<?php

namespace App\Http\Controllers;

use App\Jobs\EWTransactionCount;
use App\Models\EWFrequency;
use App\Repositories\EWalletLimitConfig;
use App\Repositories\SierraSMSProvider;
use App\Repositories\SMSHandler;
use Illuminate\Support\Facades\DB;
use App\Models\EWTrxHistory;
use App\Models\EWUserLimitConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class HealthCheckController extends Controller
{
    public $trx_data;

    public function healthCheck()
    {
        try {
            DB::connection()
              ->getPdo()
            ;
            $checkData['mysql'] = 'up';
        } catch (\Exception $e) {
            $checkData['mysql'] = 'down';
        }

        if ((bool)env("MONITOR_ACCOUNTING") == true) {
            $checkData['accounting'] = 'down';
            $accountingStatus        = $this->getHttpResponseCode(env("CGW_BASE_URL"), false);
            if ($accountingStatus !== 0 && $accountingStatus != 500) {
                $checkData['accounting'] = 'up';
            }
        }

        if ((bool)env("MONITOR_COMMISSION") == true) {
            $checkData['commission'] = 'down';
            $commissionStatus        = $this->getHttpResponseCode(env("COMMISSION_BASE_URL"), false);
            if ($commissionStatus !== 0 && $commissionStatus != 500) {
                $checkData['commission'] = 'up';
            }
        }

        if ((bool)env("MONITOR_PRODUCT") == true) {
            $checkData['product'] = 'down';
            $productStatus        = $this->getHttpResponseCode(env("CBS_PRODUCT_BASE_URL"), false);
            if ($productStatus !== 0 && $productStatus != 500) {
                $checkData['product'] = 'up';
            }
        }

        if ((bool)env("MONITOR_PRODUCT_RATING") == true) {
            $checkData['product_rating'] = 'down';
            $productRatingStatus         = $this->getHttpResponseCode(env("CBS_PRODUCT_RATING_BASE_URL"), false);
            if ($productRatingStatus !== 0 && $productRatingStatus != 500) {
                $checkData['product_rating'] = 'up';
            }
        }


        return $checkData;
    }

    private function getHttpResponseCode($url, $followredirects = true)
    {
        // returns int responsecode, or false (if url does not exist or connection timeout occurs)
        // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
        // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
        // if $followredirects == true : return the LAST  known httpcode (when redirected)
        if (!$url || !is_string($url)) {
            return false;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);            // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);            // dont need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // catch output (do NOT print!)
        if ($followredirects) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);  // fairly random number, but could prevent unwanted endless redirects with followlocation=true
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        }
        //      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        //      @curl_setopt($ch, CURLOPT_TIMEOUT        ,6);   // fairly random number (seconds)... but could prevent waiting forever to get a result
        //      @curl_setopt($ch, CURLOPT_USERAGENT      ,"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1");   // pretend we're a regular browser
        curl_exec($ch);
        if (curl_errno($ch)) {   // should be 0
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code;
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
        curl_close($ch);
        return $code;
    }
}
