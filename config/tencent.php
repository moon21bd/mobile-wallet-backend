<?php

return [
    'secretId'    => env('SIERRA_SECRET_ID', 'AKIDkGDuMG9PI3b2D8mDPCltc6MM8xVsisTy'),
    'secretKey'   => env('SIERRA_SECRET_KEY', 'umTxFRYRFOIxLTO4TklY6s8ACsKUBfso'),
    'smsSdkAppId' => env('SIERRA_SMS_SDK_APP_ID', '1400456885'),
    'region'      => env('SIERRA_REGION', 'ap-singapore'),
    'signMethod'  => env('SIERRA_SIGN_METHOD', 'HmacSHA256'),
    'endpoint'    => env('SIERRA_ENDPOINT', 'sms.ap-singapore.sierracloudapi.com'),
    'signName'    => env('SIERRA_SIGN_NAME', 'Whiskey'),
    'senderId'    => env('SIERRA_SENDER_ID', ''),
    'templates'   => [
        'OTP-signup-la'           => '2907056',
        'OTP-signup-en'           => '2907055',
        'OTP-forgot-password-en'  => '2907057',
        'OTP-forgot-password-la'  => '2907058',
        'OTP-transaction-en'      => '2907059',
        'OTP-transaction-la'      => '2907060',
        'txn-success-received-en' => '2907061',
        'txn-success-received-la' => '2907062',
        'txn-success-transfer-en' => '2907063',
        'txn-success-transfer-la' => '2907064',
        'txn-success-paid-en'     => '2907065',
        'txn-success-paid-la'     => '2907066',
    ]
];
