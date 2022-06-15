<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\EWOTPHistory;
use App\Models\EWOTPUsageLog;
use App\Models\EWTrxActivityType;
use App\Models\EWTrxHistory;
use App\Models\UserSubscription;
use App\Repositories\CoreHandler;
use App\Repositories\EWalletHandler;
use App\Repositories\RateHandler;
use App\Repositories\SMSHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\Validator;

class TransactionApiService extends ResponseController
{
    public $defaultUserWalletId = 1;
    public $appId               = 1;

    public function verifyPin($pinFromDb, $pinFromRequest)
    {
        // VERIFY USER PIN
        if ($pinFromDb != md5($pinFromRequest)) {
            return false;
        }
        return true;
    }

    public function verifySender($receiverId)
    {
        $verifiedSenderId = AdminUser::where('username', $receiverId)
                                     ->first()
        ;
        if (!$verifiedSenderId) {
            return false;
        }
        return $verifiedSenderId;
    }

    public function senderSubscriptionCheck($trxAmount, $senderId, $language)
    {
        $senderSubscriptionCheck = $this->checkSubscriptionBalance($this->defaultUserWalletId, $senderId, $language);

        if (!$senderSubscriptionCheck['status'] && ($senderSubscriptionCheck['type'] == 'wallet_account_failed')) {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'wallet_account_failed',
                'message_key'       => 'WALLET_ACCOUNT_FAILED',
                'message'           => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED'),
                'message_with_lang' => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED', true)
            ];
        } else {
            if (!$senderSubscriptionCheck['status'] && ($senderSubscriptionCheck['type'] == 'not_enough_balance')) {
                return [
                    'code'              => Response::HTTP_PAYMENT_REQUIRED,
                    'status'            => 'error',
                    'type'              => 'not_enough_balance',
                    'message_key'       => 'NOT_ENOUGH_BALANCE',
                    'message'           => $this->validationMsg($language, 'NOT_ENOUGH_BALANCE'),
                    'message_with_lang' => $this->validationMsg($language, 'NOT_ENOUGH_BALANCE', true)
                ];
            } else {
                if ($senderSubscriptionCheck['status'] && ($senderSubscriptionCheck['type'] == 'balance_found')) {
                    $senderRemainingBalance = $senderSubscriptionCheck['balance'];
                }
            }
        }

        if ($trxAmount > $senderRemainingBalance) {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'low_balance_for_transaction',
                'message_key'       => 'LOW_BALANCE_FOR_TRANSACTION',
                'message'           => $this->validationMsg($language, 'LOW_BALANCE_FOR_TRANSACTION'),
                'message_with_lang' => $this->validationMsg($language, 'LOW_BALANCE_FOR_TRANSACTION', true)
            ];
        } else {
            return [
                'code'              => Response::HTTP_OK,
                'status'            => 'success',
                'type'              => 'transaction_ok',
                'message_key'       => 'TRANSACTION_OK',
                'message'           => $this->validationMsg($language, 'TRANSACTION_OK'),
                'message_with_lang' => $this->validationMsg($language, 'TRANSACTION_OK', true)
            ];
        }
    }

    public function receiverSubscriptionCheck($receiverId, $language)
    {
        $receiverSubscriptionCheck = $this->checkSubscriptionBalance($this->defaultUserWalletId, $receiverId, $language);

        if ($receiverSubscriptionCheck['status'] === false && $receiverSubscriptionCheck['type'] == 'wallet_account_failed') {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'wallet_account_failed',
                'message_key'       => 'WALLET_ACCOUNT_FAILED',
                'message'           => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED'),
                'message_with_lang' => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED', true)
            ];
        }

        return [
            'code'              => Response::HTTP_OK,
            'status'            => 'success',
            'type'              => 'wallet_account_already_created',
            'message_key'       => 'WALLET_ACCOUNT_ALREADY_CREATED',
            'message'           => $this->validationMsg($language, 'WALLET_ACCOUNT_ALREADY_CREATED'),
            'message_with_lang' => $this->validationMsg($language, 'WALLET_ACCOUNT_ALREADY_CREATED', true)
        ];
    }

    public function deductBalanceFromSender($senderId, $receiverId, $totalTrxAmount, $trxActivityType, $trxNote, $actualTrxAmount, $transactionReference, $trxFee, $additionalData = [], $language)
    {
        $subscriptionDetail = UserSubscription::where(['user_id' => $senderId, 'subscription_category_id' => $this->defaultUserWalletId])
                                              ->first()
        ;

        if (null === $subscriptionDetail) {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'subscription_not_found',
                'message'           => $this->validationMsg($language, 'SUBSCRIPTION_NOT_FOUND'),
                'message_key'       => 'SUBSCRIPTION_NOT_FOUND',
                'message_with_lang' => $this->validationMsg($language, 'SUBSCRIPTION_NOT_FOUND', true)
            ];
        }

        $cgwRefId              = $subscriptionDetail->core_ref_id ?? 0;
        $comment               = "DEDUCT BALANCE FROM SENDER ID : ".$senderId.". BALANCE WILL BE GIFTED TO ID : ".$receiverId;
        $deductBalanceResponse = (new CoreHandler())->deductBalance($cgwRefId, $totalTrxAmount, $subscriptionDetail, $comment);

        if ($deductBalanceResponse === Response::HTTP_OK) {
            // getting user current balance
            $currentBalance = (new CoreHandler())->getBalance($cgwRefId);
            $isCharge       = 'no';
            if ($additionalData['is_charge'] == 'yes') {
                $isCharge = 'yes';
            }

            // add history to transaction history table
            return $this->saveTransactionHistory([
                'sender_user_id'       => $senderId,
                'receiver_user_id'     => $receiverId,
                'trx_activity_type_id' => $trxActivityType,
                'owner_id'             => $senderId,
                'trx_amount'           => $actualTrxAmount,
                'trx_type'             => "out",
                'trx_status'           => 1,
                'is_charge'            => $isCharge,
                'trx_reference'        => $transactionReference,
                'balance'              => $currentBalance ?? 0,
                'trx_note'             => $trxNote ?? "",
                'trx_uuid'             => $this->getUUID($senderId),
                'created_by'           => $senderId,
                'updated_by'           => $senderId
            ], 'sender');
        } else {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'balance_deduct_error',
                'message'           => $this->validationMsg($language, 'BALANCE_DEDUCT_ERROR'),
                'message_key'       => 'BALANCE_DEDUCT_ERROR',
                'message_with_lang' => $this->validationMsg($language, 'BALANCE_DEDUCT_ERROR', true)
            ];
        }
    }

    public function addTransactionChargeForSender($senderId, $receiverId, $trxAmount, $trxActivityType, $trxNote, $transactionReference, $currentBalance)
    {
        // add transfer fee history to transaction history table
        return $this->saveTransactionHistory([
            'sender_user_id'       => $senderId,
            'receiver_user_id'     => $receiverId,
            'trx_activity_type_id' => $trxActivityType,
            'owner_id'             => $senderId,
            'trx_amount'           => $trxAmount,
            'trx_type'             => "out",
            'trx_status'           => 1,
            'is_charge'            => "yes",
            'trx_reference'        => $transactionReference,
            'balance'              => $currentBalance ?? 0,
            'trx_note'             => $trxNote ?? "",
            'trx_uuid'             => $this->getUUID($senderId),
            'created_by'           => $senderId,
            'updated_by'           => $senderId
        ], 'sender');
    }

    public function sendBalanceToReceiverAccount($receiverId, $senderId, $trxAmount, $trxActivityType, $trxNote, $transactionReference, $additionalData = [], $language)
    {
        $subscriptionDetail = UserSubscription::where(['user_id' => $receiverId, 'subscription_category_id' => $this->defaultUserWalletId])
                                              ->first()
        ;
        if (null === $subscriptionDetail) {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'receiver_subscription_not_found',
                'message'           => $this->validationMsg($language, 'RECEIVER_SUBSCRIPTION_NOT_FOUND'),
                'message_key'       => 'RECEIVER_SUBSCRIPTION_NOT_FOUND',
                'message_with_lang' => $this->validationMsg($language, 'RECEIVER_SUBSCRIPTION_NOT_FOUND', true),
            ];
        }

        // $getReceiverTrxActivity = EWTrxActivityType::find($trxActivityType);

        $cgwRefId           = $subscriptionDetail->core_ref_id ?? 0;
        $comment            = "ADD BALANCE TO RECEIVER ID : ".$receiverId.". BALANCE WILL BE CUT FROM ID : ".$senderId;
        $addBalanceResponse = (new CoreHandler())->addBalance($cgwRefId, $trxAmount, $subscriptionDetail, $comment);

        $receiverUserId = $receiverId;
        $isCharge       = 'no';
        if ($additionalData['is_charge'] == 'yes') {
            $isCharge       = 'yes';
            $receiverUserId = $additionalData['receiver_id'];
        }
        if ($addBalanceResponse === Response::HTTP_OK) {
            // write log to transaction table
            $currentBalance = (new CoreHandler())->getBalance($cgwRefId);
            return $this->saveTransactionHistory([
                'sender_user_id'       => $senderId,
                'receiver_user_id'     => $receiverUserId,
                'trx_activity_type_id' => $this->getReceiverActivityTypeId($trxActivityType),
                'trx_amount'           => $trxAmount,
                'owner_id'             => $receiverId,
                'balance'              => $currentBalance ?? 0,
                'trx_type'             => "in",
                'trx_status'           => 1,
                'trx_reference'        => $transactionReference,
                'is_charge'            => $isCharge,
                'trx_note'             => $trxNote ?? "",
                'trx_uuid'             => $this->getUUID($receiverId),
                'created_by'           => $receiverId,
                'updated_by'           => $receiverId
            ], 'receiver');
        } else {
            return [
                'code'              => Response::HTTP_PAYMENT_REQUIRED,
                'status'            => 'error',
                'type'              => 'receiver_trx_history_adding_failed',
                'message'           => $this->validationMsg($language, 'ADD_BALANCE_FAILED').' '.$senderId,
                'message_key'       => 'ADD_BALANCE_FAILED',
                'message_with_lang' => $this->validationMsg($language, 'ADD_BALANCE_FAILED', true),
            ];
        }
    }

    private function saveTransactionHistory($params = [], $senderOrReceiver)
    {
        $addTransaction = EWTrxHistory::create([
            'sender_user_id'       => $params['sender_user_id'],
            'receiver_user_id'     => $params['receiver_user_id'],
            'trx_activity_type_id' => $params['trx_activity_type_id'],
            'trx_amount'           => $params['trx_amount'],
            'owner_id'             => $params['owner_id'],
            'balance'              => $params['balance'],
            'trx_type'             => $params['trx_type'],
            'trx_status'           => 1,
            'trx_reference'        => $params['trx_reference'],
            'is_charge'            => $params['is_charge'],
            'trx_uuid'             => $params['trx_uuid'],
            'trx_note'             => $params['trx_note'],
            'created_by'           => $params['created_by'],
            'updated_by'           => $params['updated_by']
        ]);

        if ($addTransaction) {
            return [
                'code'        => Response::HTTP_CREATED,
                'status'      => 'success',
                'type'        => $senderOrReceiver.'_balance_added_trx_history_added',
                'message'     => 'Transaction history added for '.$senderOrReceiver,
                'message_key' => $senderOrReceiver.'_balance_added_trx_history_added'
            ];
        }

        return [
            'code'        => Response::HTTP_PAYMENT_REQUIRED,
            'status'      => 'error',
            'type'        => $senderOrReceiver.'_trx_history_adding_failed',
            'message'     => 'Transaction history adding failed for '.$senderOrReceiver,
            'message_key' => $senderOrReceiver.'_trx_history_adding_failed',
        ];
    }

    public function getUUID($userId = 0)
    {
        return strtoupper(uniqid(rand(1, 1000000))).$userId;
    }

    public function checkSubscriptionBalance($serviceId = 0, $userId = 0, $language): array
    {
        // update core balance
        $subscriptionDetail = UserSubscription::where(['user_id' => $userId, 'subscription_category_id' => $serviceId])
                                              ->first()
        ;

        if (isset($subscriptionDetail->core_ref_id) && !empty($subscriptionDetail->core_ref_id)) {
            $balance = (new CoreHandler())->getBalance($subscriptionDetail->core_ref_id);

            if (!$balance || ($balance <= 0)) {
                $returnArr = [
                    'status'  => false,
                    'type'    => 'not_enough_balance',
                    'message' => 'not enough balance.'
                ];
            } else {
                $returnArr = [
                    'status'  => true,
                    'type'    => 'balance_found',
                    'balance' => $balance
                ];
            }
        } else {
            $returnArr = [
                'status'            => false,
                'type'              => 'wallet_account_failed',
                'message'           => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED'),
                'message_with_lang' => $this->validationMsg($language, 'WALLET_ACCOUNT_FAILED', true)
            ];
        }

        return $returnArr;
    }

    public function getReceiverActivityTypeId($trxActivityType)
    {
        $getReceiverTrxActivity = EWTrxActivityType::find($trxActivityType);
        return $getReceiverTrxActivity ? $getReceiverTrxActivity->receiver_type_id : 0;
    }

    public function getTrxRateByActivityTypeId($trxActivityType, $trxAmount = 0): array
    {
        $getTrxRate = RateHandler::getRate([
            'appId'           => $this->appId,
            'amount'          => $trxAmount,
            'trxActivityType' => $trxActivityType,
            'productId'       => 1
        ]);

        $trxRateTotal = (is_array($getTrxRate) && array_key_exists('total', $getTrxRate)) ? $getTrxRate['total'] : 0;
        $trxRate      = $trxRateTotal;

        return [
            'rate'         => $trxRate,
            'total_deduct' => ($trxRate + $trxAmount)
        ];
    }

    public function generateTransactionReference($userId = 0): string
    {
        return uniqid();
    }

    /**
     * @param  string  $phoneNumber
     * @return array
     */
    public function generateAndSendOtp(string $phoneNumber, string $channel = "transaction", string $trxRef = null, string $type = 'regular', string $language = 'en'): array
    {
        //phone number special character removed
        $phoneNumber = $this->processPhone($phoneNumber);
        if ($type == 'resend') {
            //get today total resend otp count
            $sentOtpCount   = EWOTPHistory::where('mobile_no', $phoneNumber)
                                          ->where('channel', $channel)
                                          ->where('type', 'resend')
                                          ->where('ref_id', $trxRef)
                                          ->whereDate('created_at', Carbon::today())
                                          ->count()
            ;
            $max_otp_config = ($channel == 'register') ? 'api.max_otp_for_registration' : 'api.max_resend_otp_for_transaction';

            if ($sentOtpCount >= config($max_otp_config)) {
                return [
                    'status'      => false,
                    'code'        => Response::HTTP_BAD_REQUEST,
                    'message'     => $this->validationMsg($language, 'OTP_LIMIT_EXCEEDED'),
                    'message_key' => 'OTP_LIMIT_EXCEEDED',
                    'limit_over'   => true
                ];
            }
        }

        //generate the otp
        if (env('APP_ENV') == 'local') {
            $otp = "1234";
        } else {
            $otp = (string)$this->getRand();
        }

        //save the OTP History
        $otpHistory                = new EWOTPHistory();
        $otpHistory->mobile_no     = $phoneNumber;
        $otpHistory->otp           = $otp;
        $otpHistory->channel       = $channel;
        $otpHistory->otp_used      = 'no';
        $otpHistory->type          = $type;
        $otpHistory->ref_id        = $trxRef;
        $otpHistory->code_lifetime = Carbon::now()
                                           ->addMinute(10)
        ;

        //check local
        if (env('APP_ENV') != 'local') {
            $template_code = '';
            if ($channel == 'register') {
                $template_code = 'OTP-signup-'.$language;
            } elseif ($channel == 'transaction') {
                $template_code = 'OTP-transaction-'.$language;
            }
            $response = SMSHandler::send_sms_notification($phoneNumber, $template_code, [$otp]);
        } else {
            $response = [
                'status' => true,
                'code'   => Response::HTTP_OK,
                'msg'    => 'SMS Sent Successfully'
            ];
        }
        //save
        $otpHistory->smsgw_response = json_encode($response);
        $otpHistory->save();

        if (empty($response)) {
            return [
                'status'      => false,
                'code'        => Response::HTTP_BAD_REQUEST,
                'message'     => $this->validationMsg($language, 'OTP_ERROR'),
                'message_key' => 'OTP_ERROR',
                'limit_over'   => false
            ];
        }

        //final success
        return [
            'status'      => true,
            'code'        => Response::HTTP_OK,
            'message'     => $this->validationMsg($language, 'OTP_SUCCESS'),
            'message_key' => 'OTP_SUCCESS'
        ];
    }

    public function otpVerify(string $phone, string $otp, string $trx_ref = null, string $channel = 'transaction', string $language = 'en')
    {
        $phone    = $this->processPhone($phone);
        $givenOtp = trim($otp);
        $checkOtp = EWOTPHistory::where('mobile_no', $phone)
                                ->where('otp_used', 'no')
                                ->where('channel', $channel)
                                ->where('ref_id', $trx_ref)
                                ->orderBy('id', 'desc')
                                ->first()
        ;

        if (!$checkOtp) {
            return [
                'status'      => false,
                'code'        => Response::HTTP_BAD_REQUEST,
                'message'     => $this->validationMsg($language, 'INVALID_OTP'),
                'message_key' => 'INVALID_OTP',
            ];
        }

        if ($checkOtp->code_lifetime <= Carbon::now()) {
            //save otp uses history
            $saveOtpUsageLog = EWOTPUsageLog::create([
                'mobile_no' => $phone,
                'otp'       => $givenOtp,
                'channel'   => $channel,
                'is_valid'  => 'no'
            ]);

            return [
                'status'      => false,
                'code'        => Response::HTTP_BAD_REQUEST,
                'message'     => $this->validationMsg($language, 'OTP_EXPIRED'),
                'message_key' => 'OTP_EXPIRED',
            ];
        }

        if ($checkOtp->otp != $givenOtp) {
            //save otp uses log history
            $saveOtpUsageLog = EWOTPUsageLog::create([
                'mobile_no' => $phone,
                'otp'       => $givenOtp,
                'channel'   => $channel,
                'is_valid'  => 'no'
            ]);
            return [
                'status'      => false,
                'code'        => Response::HTTP_BAD_REQUEST,
                'message'     => $this->validationMsg($language, 'OTP_NOT_MATCHED'),
                'message_key' => 'OTP_NOT_MATCHED',
            ];
        }

        // for matched otp
        $checkOtp->otp_used   = "yes";
        $checkOtp->updated_at = Carbon::now();
        $checkOtp->save();

        // also save to usage history
        $saveOtpUsageLog = EWOTPUsageLog::create([
            'mobile_no' => $phone,
            'otp'       => $givenOtp,
            'channel'   => $channel,
            'is_valid'  => 'yes'
        ]);

        return [
            'status'      => true,
            'code'        => Response::HTTP_OK,
            'message'     => $this->validationMsg($language, 'OTP_MATCHED'),
            'message_key' => 'OTP_MATCHED',
        ];
    }

    private function processPhone(string $phone)
    {
        if (!$phone || is_null($phone)) {
            return;
        }
        $phone = str_replace("+", "", trim($phone));
        return $phone;
    }

    private function getRand($digits = 4)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
}
