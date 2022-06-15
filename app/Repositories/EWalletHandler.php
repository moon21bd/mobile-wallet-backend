<?php

namespace App\Repositories;

use App\Models\Platform\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Facades\Admin;

class EWalletHandler
{
    public function saveToken($user)
    {
        $user->api_token = $user->id . uniqid();
        return $user->save();
    }

    public function addRole($userID, $roleID)
    {
        $roleData = [
            'role_id' => $roleID,
            'user_id' => $userID,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        return DB::table('admin_role_users')->insert($roleData);
    }

    public function createCBSAccount($user)
    {
        $subscription_category_id = 1; // ewallet tbl: fw_services
        $serviceName = 'eWallet';
        $createdBy = 'system';
        $subscription = new \App\Models\UserSubscription();
        $subscription->user_id = $user->id;
        $subscription->subscription_category_id = $subscription_category_id;
        $subscription->is_subscribed = false;
        if ($subscription->save()) {
            $subscriptionID = $subscription->id;
            $CoreHandlerObj = new \App\Repositories\CoreHandler();
            $result = $CoreHandlerObj->createAccount((string)$user->id, $createdBy, $user->username, $serviceName, $subscriptionID);
            if ($result !== 'Duplicate UserDetails name' && $result !== 'Failed') {
                // update subscription
                $subscription->core_ref_id = $result;
                $subscription->is_subscribed = true;
                $subscription->save();
            }
            return true;
        }
        return false;
    }

    public function getCommission()
    {
        $userSubscriptionDetail = \App\Models\UserSubscription::where('user_id', Admin::user()->id)->first();
        if (!empty($userSubscriptionDetail)) {
            $CoreHandlerObj = new \App\Repositories\CoreHandler();
            return $result = $CoreHandlerObj->getBalance($userSubscriptionDetail->core_ref_id);
        }

        return 0;
    }


    public function checkSubscriptionBalance($serviceId = 0, $userId = 0): array
    {
        // update core balance
        $subscriptionDetail = UserSubscription::where(['user_id' => $userId, 'subscription_category_id' => $serviceId])->first();

        if (NULL === $subscriptionDetail) {
            return [
                'status' => false,
                'type' => 'wallet_account_failed',
                'message' => 'wallet account is not configured yet.'
            ];
        }

        $balance = (new CoreHandler())->getBalance($subscriptionDetail->core_ref_id);

        if (!$balance || ($balance <= 0)) {
            return [
                'status' => false,
                'type' => 'not_enough_balance',
                'message' => 'not enough balance.'
            ];
        }

        return [
            'status' => true,
            'type' => 'balance_found',
            'balance' => $balance
        ];
    }

}
