<?php

namespace App\Repositories;

use App\Models\TransactionLog;
use App\Models\UserSubscription;
use Encore\Admin\Facades\Admin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class CoreHandler
{
    public function getBalance($refID)
    {
        try {
            $client = new Client();
            $response = $client->get(env('CGW_BASE_URL').'/account?id='.$refID, [['verify' => true]]);
            $statusCode = $response->getStatusCode();
            $result = $response->getBody()->getContents();
            if(!empty($result)) {
                $data = json_decode($result, true);
                return empty($data['data']) ? 0 : $data['data'][0]['netBalance'];
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("CORE Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("CORE Connection Failed!", $e->getMessage());
            return 0;
        }

    }

    public function addBalance($refID, $balance, $subscriptionDetail = null, $comment = null)
    {
        if(empty($subscriptionDetail)) {
            $subscriptionDetail = UserSubscription::where('core_ref_id', $refID)->first();
        }
        if(!empty($subscriptionDetail)) {
            $transactionLog = new TransactionLog();
            $transactionLog->user_id = $subscriptionDetail->user_id;
            $transactionLog->comment = !empty($comment) ? $comment : "Got Tk. $balance";
            $transactionLog->amount = $balance;
            $transactionLog->in_out = 'IN';
            $transactionLog->transaction_type = 'Add Balance';
            $transactionLog->created_by = !empty(Admin::user()->id) ? Admin::user()->id : $subscriptionDetail->user_id;
            $transactionLog->save();
        }

        $client = new Client();
        $data = [
            "transactionType" => "Payment",
            "sourceId" => uniqid(),
            "transactionDate" => date('Y-m-d H:i:s'),
            "createdBy" => "Admin",
            "debit" => [
                "accountId" => $refID,
                "amount" => $balance
            ]
        ];

        Log::info('CORE::addBalance Request: '.json_encode($data));

        $response = $client->post(env('CGW_BASE_URL').'/accounting-transaction', [
            RequestOptions::JSON => $data
        ]);

        $result = $response->getBody()->getContents();
        Log::info('CORE::addBalance Response: '.$result);

        $data = json_decode($result, true);
        return $data['status'];
    }

    public function deductBalance($refID, $balance, $subscriptionDetail = null, $comment = null)
    {
        if(empty($subscriptionDetail)) {
            $subscriptionDetail = UserSubscription::where('core_ref_id', $refID)->first();
        }
        if(!empty($subscriptionDetail)) {
            $transactionLog = new TransactionLog();
            $transactionLog->user_id = $subscriptionDetail->user_id;
            $transactionLog->comment = !empty($comment) ? $comment : "Deduct Tk. $balance";
            $transactionLog->amount = $balance;
            $transactionLog->in_out = 'OUT';
            $transactionLog->transaction_type = 'Deduct Balance';
            $transactionLog->created_by = !empty(Admin::user()->id) ? Admin::user()->id : $subscriptionDetail->user_id;
            $transactionLog->save();
        }
        try {
            $client = new Client();
            $data = [
                "transactionType" => "Sale",
                "sourceId" => uniqid(),
                "transactionDate" => date('Y-m-d H:i:s'),
                "createdBy" => "Admin",
                "credit" => [
                    "accountId" => $refID,
                    "amount" => $balance
                ]
            ];
			
			Log::info('CORE::deductBalance Request: ' . json_encode($data));

			$response = $client->post(env('CGW_BASE_URL') . '/accounting-transaction', [
				RequestOptions::JSON => $data
			]);

			$result = $response->getBody()->getContents();

			Log::info('CORE::deductBalance Response: ' . $result);
			
            if(!empty($result)) {
                $data = json_decode($result, true);
                return $data['status'];
            } else{
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("CORE Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("CORE Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public function createAccount($userID, $createdBy, $userName, $serviceName, $userSubscriptionID )
    {
        $client = new Client();
        $requestData = [
            "code" => $userID,
            "createdBy" => $createdBy,
            "generalLedgerAccountId" => env('GENERAL_LEDGER_ACCOUNT'),
            "name" => $userName.'-'.$serviceName.'-'.$userID,
            "productId" => strval($userSubscriptionID)
        ];

        Log::info('CORE::Creating new account: '.json_encode($requestData));

        $response = $client->post(env('CGW_BASE_URL').'/account', [
            RequestOptions::JSON => $requestData
        ]);

        $result = $response->getBody()->getContents();
        Log::info('CORE::Creating new account response: '.json_encode($result));
        $data = json_decode($result, true);
        if ($data['status'] == 200) {
            return empty($data['data']) ? 'Failed' : $data['data'][0]['id'];
        } else if ($data['status'] == 400) {
            return $data['details'];
        } else {
            return 'Failed';
        }
    }
}
