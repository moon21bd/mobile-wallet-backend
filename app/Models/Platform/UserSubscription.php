<?php

namespace App\Models\Platform;

use Platform\Admin\Models\UserSubscription as UserSubscriptionParent;
use Platform\Admin\Repositories\CoreHandler;

class UserSubscription extends UserSubscriptionParent
{
    protected $appends = ['balance'];

    public function getBalanceAttribute()
    {
        if (!empty($this->attributes['core_ref_id'])) {
            $CoreHandlerObj = new CoreHandler();
            $result = $CoreHandlerObj->getBalance($this->attributes['core_ref_id']);
            $balance = number_format($result, 2);
        } else $balance = 0;

        return $balance;
    }
}
