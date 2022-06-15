<?php

return [
    'notification_api_url' => env('SMS_OTP_URL', 'https://sms.provider.example/api/otp'),
    'api_url' => env('SMS_SEND_URL', 'https://sms.provider.example/api/send'),
    'masking' => "NOMASK",
    'userName' => "eWallet",
    'password' => "7126a16515c6cf76b5aa2c18acf875c9",
    'MsgType' => "TEXT",
    'return_params' => [
        'success', 'message', 'msgid', 'jobid', 'msisdn'
    ],
    'confirmation_param_name' => 'success',
    'confirmation_param_value' => 1,
    'retry_count' => 3,
];
