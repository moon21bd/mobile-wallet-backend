<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use App\Models\EWSMSHistory;


class SMSHandler
{

    public static function send_sms_notification($mobile_no, $message, $extra_params = [])
    {
        $response         = [
            'status' => false,
            'code'   => 400,
            'msg'    => 'Sorry. SMS not sent',
        ];
        $provider_response = [];
        $request_data     = ['mobile_no' => $mobile_no, 'message' => $message, 'extra_params' => $extra_params];
        $status           = 'failed';
        if(env('app_env')!='local') {
            if (env('SMS_PROVIDER') == 'sierra') {
                //generate mobile no as array
                $mobile_no_list[] = $mobile_no;
                //get message to template id
                $templates        = config('sierra.templates');
                $template_id      = isset($templates[$message]) ? $templates[$message] : 0;
                $provider_response = SierraSMSProvider::sendSMS($mobile_no_list, $template_id, $extra_params);
                if ($provider_response['status']) {
                    $status             = 'sent';
                    $response['status'] = true;
                    $response['code']   = 200;
                    $response['msg']    = 'SMS Sent Successfully';
                }
            } elseif (env('SMS_PROVIDER') == 'tango') {
                $sms_provider      = new TangoSMSProviderHandler();
                $provider_response = $sms_provider->sendSMS($mobile_no, $message);
                if ($provider_response['status']) {
                    $status             = 'sent';
                    $response['status'] = true;
                    $response['code']   = 200;
                    $response['msg']    = 'SMS Sent Successfully';
                }
            }
        }else{
            $provider_response=['status'=>true,'msg'=>'Message sent success','message_id'=>session()->getId()];
            $status             = 'sent';
            $response['status'] = true;
            $response['code']   = 200;
            $response['msg']    = 'SMS Sent Successfully';
        }

        //save the sms history
        $sms_history                = new EWSMSHistory();
        $sms_history->mobile_no     = $mobile_no;
        $sms_history->sms_provider   = env('SMS_PROVIDER');
        $sms_history->request_data  = json_encode($request_data);
        $sms_history->response_data = json_encode($provider_response);
        $sms_history->status        = $status;
        $sms_history->save();

        return $response;
    }

}
