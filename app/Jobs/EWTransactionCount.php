<?php

namespace App\Jobs;

use App\Models\EWTrxHistory;
use App\Models\EWUserLimitConfig;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EWFrequency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EWTransactionCount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $trx_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->trx_data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //get all frequency
        $all_frequencies = EWFrequency::all();
        foreach ($all_frequencies as $frequency) {
            if ($frequency->unit == 'day') {
                $trx_count = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereDay('created_at', Carbon::now()->day)->count();
                $trx_amount = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereDay('created_at', Carbon::now()->day)->sum('trx_amount');
                $user_limit_config = EWUserLimitConfig::where('user_id', $this->trx_data['user_id'])->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('frequency_id', $frequency->id)->first();
                if ($user_limit_config) {
                    $user_limit_config->total_trx_amount = $trx_amount;
                    $user_limit_config->total_trx_count = $trx_count;
                    $user_limit_config->save();
                }
            } elseif ($frequency->unit == 'month') {
                $trx_count = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereMonth('created_at', Carbon::now()->month)->count();
                $trx_amount = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereMonth('created_at', Carbon::now()->month)->sum('trx_amount');
                $user_limit_config = EWUserLimitConfig::where('user_id', $this->trx_data['user_id'])->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('frequency_id', $frequency->id)->first();
                if ($user_limit_config) {
                    $user_limit_config->total_trx_amount = $trx_amount;
                    $user_limit_config->total_trx_count = $trx_count;
                    $user_limit_config->save();
                }
            } elseif ($frequency->unit == 'year') {
                $trx_count = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereYear('created_at', Carbon::now()->year)->count();
                $trx_amount = EWTrxHistory::where('owner_id', $this->trx_data['user_id'])
                    ->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('is_charge', 'no')
                    ->whereYear('created_at', Carbon::now()->year)->sum('trx_amount');
                $user_limit_config = EWUserLimitConfig::where('user_id', $this->trx_data['user_id'])->where('trx_activity_type_id', $this->trx_data['trx_activity_type_id'])
                    ->where('frequency_id', $frequency->id)->first();
                if ($user_limit_config) {
                    $user_limit_config->total_trx_amount = $trx_amount;
                    $user_limit_config->total_trx_count = $trx_count;
                    $user_limit_config->save();
                }
            }
        }
    }
}
