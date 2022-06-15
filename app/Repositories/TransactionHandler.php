<?php

namespace App\Repositories;

use App\Jobs\EWSendTransactionNotification;
use App\Jobs\EWTransactionCount;
use App\Services\TransactionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TransactionHandler
{
    //transaction eligibility check
    public static function trxEligibilityCheck($senderId, $receiverId, $trxAmount, $trxActivityType, $language = 'en'): array
    {
        $response = [
            'status' => false
        ];
        //transaction api service
        $transactionApiService = new TransactionApiService();

        //transaction Fee
        $trxRate        = $transactionApiService->getTrxRateByActivityTypeId($trxActivityType, $trxAmount);
        $totalTrxAmount = $trxRate['total_deduct'];

        // SENDER WALLET CHECKING
        $senderSubscriptionCheck = $transactionApiService->senderSubscriptionCheck($totalTrxAmount, $senderId, $language);
        if ($senderSubscriptionCheck['status'] == 'error' && $senderSubscriptionCheck['code'] == Response::HTTP_PAYMENT_REQUIRED) {
            $response['message']     = $senderSubscriptionCheck['message'];
            $response['message_key'] = $senderSubscriptionCheck['message_key'];
            $response['code']        = $senderSubscriptionCheck['code'];
            return $response;
        }

        // RECEIVER WALLET CHECKING
        $receiverSubscriptionCheck = $transactionApiService->receiverSubscriptionCheck($receiverId, $language);
        if ($receiverSubscriptionCheck['status'] == 'error' && ($receiverSubscriptionCheck['code'] == Response::HTTP_PAYMENT_REQUIRED)) {
            $response['message']     = $receiverSubscriptionCheck['message'];
            $response['message_key'] = $receiverSubscriptionCheck['message_key'];
            $response['code']        = $receiverSubscriptionCheck['code'];
            return $response;
        }

        // Limit Check for sender
        $senderTrxLimitCheck = EWalletLimitConfig::checkTrxLimit($senderId, $trxActivityType, $trxAmount, 'send', $language);
        if (!$senderTrxLimitCheck['status']) {
            $response['message']     = $senderTrxLimitCheck['message'];
            $response['message_key'] = $senderTrxLimitCheck['message_key'];
            $response['code']        = Response::HTTP_BAD_REQUEST;
            return $response;
        }

        // Limit check for receiver
        $receiverActivityTypeId = $transactionApiService->getReceiverActivityTypeId($trxActivityType);
        $receiverTrxLimitCheck  = EWalletLimitConfig::checkTrxLimit($receiverId, $receiverActivityTypeId, $trxAmount, 'received', $language);
        if (!$receiverTrxLimitCheck['status']) {
            $response['message']     = $receiverTrxLimitCheck['message'];
            $response['message_key'] = $receiverTrxLimitCheck['message_key'];
            $response['code']        = Response::HTTP_BAD_REQUEST;
            return $response;
        }

        //for everything ok
        $response['status']      = true;
        $response['message']     = 'Operation Successful.';
        $response['message_key'] = 'OPERATION_SUCCESSFUL';
        $response['code']        = Response::HTTP_OK;
        $response['data']        = [
            'trx_fee'                   => $trxRate['rate'],
            'trx_total'                 => $trxRate['total_deduct'],
            'receiver_activity_type_id' => $receiverActivityTypeId,
        ];
        return $response;
    }

    //transaction process
    public static function doTransaction($senderId, $receiverId, $trxAmount, $trxActivityType, $trxNote, $transID = null, $language = 'en'): array
    {
        $response = [
            'status' => false
        ];
        // You cannot Send Money to your own account.
        if ($senderId === $receiverId) {
            $response['message']     = 'You cannot Send Money to your own account.';
            $response['message_key'] = 'OWN_SEND_MONEY';
            $response['code']        = Response::HTTP_FORBIDDEN;
            return $response;
        }

        //check transaction eligibility
        $trxCheckResponse = self::trxEligibilityCheck($senderId, $receiverId, $trxAmount, $trxActivityType, $language);
        if (!$trxCheckResponse['status']) {
            $response['message']     = $trxCheckResponse['message'];
            $response['message_key'] = $trxCheckResponse['message_key'];
            $response['code']        = $trxCheckResponse['code'];
            return $response;
        }

        // TRANSACTION FEE CONFIGURED AT CBS END
        $trxFee                 = $trxCheckResponse['data']['trx_fee'] ?? 0;
        $receiverActivityTypeId = $trxCheckResponse['data']['receiver_activity_type_id'] ?? 0;

        //transaction api service
        $transactionApiService = new TransactionApiService();

        //transaction reference
        if ($transID) {
            $transactionReference = $transID;
        } else {
            $transactionReference = $transactionApiService->generateTransactionReference($senderId);
        }

        /* :: BALANCE DEDUCTION AND SENDING STARTED FROM HERE :: */

        // 1. DEDUCT BASE AMOUNT FROM SENDER (AMOUNT E.G.: 100 BDT)
        $deductBalanceFromSender = $transactionApiService->deductBalanceFromSender($senderId, $receiverId, $trxAmount, $trxActivityType, $trxNote, $trxAmount, $transactionReference, $trxFee,
            ['is_charge' => 'no'], $language);

        if ($deductBalanceFromSender['status'] == 'error' && $deductBalanceFromSender['code'] == Response::HTTP_PAYMENT_REQUIRED) {
            $response['message']     = self::validationMsg($deductBalanceFromSender['message_key']);
            $response['message_key'] = $deductBalanceFromSender['message_key'];
            $response['code']        = $deductBalanceFromSender['code'];
            return $response;
        }

        // 2. DEDUCT TRX FEE CHARGE FROM SENDER (e.g.: FOR 100 BDT, FEE CONFIGURED 5 BDT OR ANY CONFIGURABLE RATE AMOUNT AT CBS END)
        if ($trxFee > 0) {
            $deductTrxFeeBalanceFromSender = $transactionApiService->deductBalanceFromSender($senderId, $receiverId, $trxFee, $trxActivityType, $trxNote, $trxFee, $transactionReference, $trxFee,
                ['is_charge' => 'yes'], $language);

            if ($deductTrxFeeBalanceFromSender['status'] == 'error' && $deductTrxFeeBalanceFromSender['code'] == Response::HTTP_PAYMENT_REQUIRED) {
                $response['message']     = self::validationMsg($deductTrxFeeBalanceFromSender['message_key']);
                $response['message_key'] = $deductTrxFeeBalanceFromSender['message_key'];
                $response['code']        = $deductTrxFeeBalanceFromSender['code'];
                return $response;
            }
        }

        // 3. ADD BALANCE TO RECEIVER ACCOUNT (E.G.: IN THIS STAGE AMOUNT SHOULD BE 100 BDT (BASE AMOUNT))
        $addBalanceToReceiverAccount = $transactionApiService->sendBalanceToReceiverAccount($receiverId, $senderId, $trxAmount, $trxActivityType, $trxNote, $transactionReference,
            ['is_charge' => 'no', 'receiver_id' => $receiverId], $language);

        // 4. ADD TRANSACTION FEE AMOUNT BALANCE TO PLATFORM OWNER ACCOUNT (e.g.: 5 BDT OR CONFIGURABLE AMOUNT FROM CBS)
        if ($trxFee > 0) {
            $platformOwnerId = env("PLATFORM_OWNER_ID");
            $transactionNote = "";
            // ADD TRX FEE BALANCE TO RECEIVER ACCOUNT
            $addBalanceToPlatformOwnerAccount = $transactionApiService->sendBalanceToReceiverAccount($platformOwnerId, $senderId, $trxFee, $trxActivityType, $transactionNote, $transactionReference,
                ['is_charge' => 'yes', 'receiver_id' => $receiverId], $language);

            Log::info('ADD_BALANCE_TO_PLATFORM_OWNER_RESPONSE: '.json_encode($addBalanceToPlatformOwnerAccount));
        }

        if ($addBalanceToReceiverAccount['status'] == 'error' && $addBalanceToReceiverAccount['code'] == Response::HTTP_PAYMENT_REQUIRED) {
            $response['message']     = self::validationMsg($addBalanceToReceiverAccount['message_key']);
            $response['message_key'] = $addBalanceToReceiverAccount['message_key'];
            $response['code']        = $addBalanceToReceiverAccount['code'];
            return $response;
        }

        // transaction count job will call here
        //transaction job for sender
        EWTransactionCount::dispatch(['user_id' => $senderId, 'amount' => $trxAmount, 'trx_activity_type_id' => $trxActivityType]);

        //transaction job for receiver
        EWTransactionCount::dispatch(['user_id' => $receiverId, 'amount' => $trxAmount, 'trx_activity_type_id' => $receiverActivityTypeId]);

        //transaction notification
        EWSendTransactionNotification::dispatch([
                'transaction_reference' => $transactionReference,
                'sender_id'             => $senderId,
                'receiver_id'           => $receiverId,
                'trx_amount'            => $trxAmount,
                'trx_fee'               => $trxFee,
                'language'              => $language
            ]);

        //final transaction response
        $response['status']      = true;
        $response['message']     = self::validationMsg('TRANSACTION_SUCCESS', $language);
        $response['message_key'] = 'TRANSACTION_SUCCESS';
        $response['code']        = Response::HTTP_OK;
        $response['data']        = [
            'transaction_ref'    => $transactionReference,
            'transaction_amount' => $trxAmount,
            'transaction_charge' => $trxFee,
            'transaction_total'  => ($trxAmount + $trxFee)
        ];
        return $response;
    }

    private static function validationMsg($messageKey, $language = "en", $asArray = false)
    {
        if ($asArray) {
            return [
                "en" => config("validation-messages.en")[$messageKey],
                "la" => config("validation-messages.la")[$messageKey]
            ];
        } else {
            return config("validation-messages.$language")[$messageKey];
        }
    }
}