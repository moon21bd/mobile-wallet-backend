<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Encryption\Encrypter;

class TokenHandler
{
    public static function createToken($username)
    {
        $encrypter = new Encrypter(config('sso.encryption_key'), 'AES-128-CBC');
        $encrypted = $encrypter->encrypt(json_encode(['user' => $username, 'created' => Carbon::now()->toDateTimeString()]));
        return $encrypted;
    }

    public static function extractToken($token)
    {
        $encrypter = new Encrypter(config('sso.encryption_key'), 'AES-128-CBC');
        $dycrypted = $encrypter->decrypt($token);
        return json_decode($dycrypted);
    }
}