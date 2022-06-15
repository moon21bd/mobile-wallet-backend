<?php

return [
    'max_otp'                        => 10, // maximum otp per day
    'max_otp_for_registration'       => 3, // maximum otp for registration
    'max_otp_for_forget_pass'        => 3, // maximum otp for password reset
    'max_otp_for_transaction'        => 100, // maximum otp for transaction
    'max_resend_otp_for_transaction' => 3, // maximum otp for transaction
    'highest_otp_usage_per_day'      => 15, // highest otp usage per day
    'otp_text'                       => 'Your OTP code is ', // otp text
    'forgot_pass_otp_msg'            => 'Your forgot password verification code is '
];
