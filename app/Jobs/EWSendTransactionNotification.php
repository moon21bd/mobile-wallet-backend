<?php

namespace App\Jobs;

use App\Models\AdminUser;
use App\Models\EWTrxHistory;
use App\Repositories\SMSHandler;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EWSendTransactionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->params = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //params
            $transactionReference = $this->params['transaction_reference'] ?? '';
            $senderId             = $this->params['sender_id'] ?? '';
            $receiverId           = $this->params['receiver_id'] ?? '';
            $trxAmount            = $this->params['trx_amount'] ?? 0;
            $trxFee               = $this->params['trx_fee'] ?? 0;
            $language             = $this->params['language'] ?? 'en';

            $senderHistory        = EWTrxHistory::where('trx_reference', $transactionReference)
                                                ->where('owner_id', $senderId)
                                                ->latest('id')
                                                ->first()
            ;
            $senderAccountBalance = number_format($senderHistory->balance, 2);
            $sender               = AdminUser::find($senderId);
            $senderName           = $sender->username;
            $senderPhone          = $sender->mobile_no;
            $sendingTime          = Carbon::parse($senderHistory->created_at)
                                          ->format('d/m/Y H:i')
            ;

            $receiverHistory        = EWTrxHistory::where('trx_reference', $transactionReference)
                                                  ->where('owner_id', $receiverId)
                                                  ->latest('id')
                                                  ->first()
            ;
            $receiverAccountBalance = number_format($receiverHistory->balance, 2);
            $receiver               = AdminUser::find($receiverId);
            $receiverName           = $receiver->username;
            $receiverPhone          = $receiver->mobile_no;
            $receivingTime          = Carbon::parse($receiverHistory->created_at)
                                            ->format('d/m/Y H:i')
            ;


            $senderSmsTemplate   = 'txn-success-transfer-'.$language;
            $receiverSmsTemplate = 'txn-success-received-'.$language;
            $trxAmount           = number_format($trxAmount, 2);

            SMSHandler::send_sms_notification($senderPhone, $senderSmsTemplate, [$sendingTime, $trxAmount, $receiverName, (string)$trxFee, $senderAccountBalance]);
            SMSHandler::send_sms_notification($receiverPhone, $receiverSmsTemplate, [$receivingTime, $trxAmount, $senderName, '0.0', $receiverAccountBalance]);
        } catch (\Exception $exception) {
            Log::error('Transaction Notification Job #'.$exception->getMessage());
        }
    }
}
