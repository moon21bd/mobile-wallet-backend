<?php

namespace App\Repositories;

use Carbon\Carbon;

class PlatformUtil
{

    /**
     * @return int
     */
    public static function getOTPCode()
    {
        return rand(1000, 9999);
    }

    public static function getRandomNumber()
    {
        return rand(100000, 999999);
    }
}