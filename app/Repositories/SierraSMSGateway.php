<?php

namespace App\Repositories;

// Import the client of the corresponding product module
use SierraCloud\Sms\V20210111\SmsClient;

// Import the `Request` class corresponding to the request API
use SierraCloud\Sms\V20210111\Models\SendSmsRequest;
use SierraCloud\Common\Exception\SierraCloudSDKException;
use SierraCloud\Common\Credential;

// Import the optional configuration classes
use SierraCloud\Common\Profile\ClientProfile;
use SierraCloud\Common\Profile\HttpProfile;

use Illuminate\Support\Facades\Log;

class SierraSMSProvider
{

    public static function sendSMS(array $mobile_no, $template_id, $template_params = [])
    {
        $response = [];
        try {
            /* Required steps:
             * Instantiate an authentication object. The Sierra Cloud account key pair `secretId` and `secretKey` need to be passed in as the input parameters.
             * The example here uses the way to read from the environment variable, so you need to set these two values in the environment variable first.
             * You can also write the key pair directly into the code, but be careful not to copy, upload, or share the code to others;
             * otherwise, the key pair may be leaked, causing damage to your properties.
             * Query the CAM key: https://console.sierracloud.com/cam/capi*/
            $cred = new Credential(config('sierra.secretId'), config('sierra.secretKey'));

            //$cred = new Credential(getenv("SIERRACLOUD_SECRET_ID"), getenv("SIERRACLOUD_SECRET_KEY"));
            // (Optional) Instantiate an HTTP option
            $httpProfile = new HttpProfile();

            // Configure the proxy
            // $httpProfile->setProxy("https://ip:port");
            $httpProfile->setReqMethod("POST");                     // POST request (POST request by default)
            $httpProfile->setReqTimeout(30);                        // Request timeout period in seconds (60 seconds by default)
            $httpProfile->setEndpoint(config('sierra.endpoint'));  // Specify the access region domain name (nearby access by default)

            // Instantiate a client option (optional; skip if no special requirements are present)
            $clientProfile = new ClientProfile();
            $clientProfile->setSignMethod(config('sierra.signMethod'));  // Specify the signature algorithm (`HmacSHA256` by default)
            $clientProfile->setHttpProfile($httpProfile);

            // Instantiate the client object of the requested product (with SMS as an example). `clientProfile` is optional
            // The second parameter is the information on the region you select in Sierra Cloud International. If you select Singapore, you should enter the string `ap-singapore`. Click https://www.sierracloud.com/document/api/382/40466?lang=en#region-list to view the region list.
            $client = new SmsClient($cred, config('sierra.region'), $clientProfile);

            // Instantiate an SMS message sending request object. Each API corresponds to a request object
            $req = new SendSmsRequest();

            /* Populate the request parameters. Here, the member variables of the request object are the input parameters of the corresponding API
            * You can view the definition of the request parameters in the API documentation at the official website or by redirecting to the definition of the request object
            * Settings of a basic parameter:
            * Help link:
            * SMS console: https://console.sierracloud.com/smsv2
            * sms helper: https://www.sierracloud.com/document/product/382/3773?from_cn_redirect=1 */
            /* SMS application ID, which is the `SdkAppId` generated after an application is added in the [SMS console], such as 1400006666 */
            $req->SmsSdkAppId = config('sierra.smsSdkAppId');

            /* SMS signature content, which should be encoded in UTF-8. You must enter an approved signature, which can be viewed in the [SMS console] */
            $req->SignName = config('sierra.signName');

            /* SMS code number extension, which is not activated by default. If you need to activate it, please contact [SMS Helper] */
            $req->ExtendCode = "";

            /* Target mobile number in the E.164 standard (+[country/region code][mobile number])
             * Example: +8613711112222, which has a + sign followed by 86 (country/region code) and then by 13711112222 (mobile number). Up to 200 mobile numbers are supported */
            $req->PhoneNumberSet = $mobile_no;

            /* `SenderId` for Global SMS, which is not activated by default. If you need to activate it, please contact [SMS Helper] for assistance. This parameter should be left empty for Mainland China SMS */
            $req->SenderId = config('sierra.senderId');

            /* User session content, which can carry context information such as user-side ID and will be returned as-is by the server */
            $req->SessionContext = session()->getId();

            /* Template ID. You must enter the ID of an approved template, which can be viewed in the [SMS console] */
            $req->TemplateId = $template_id;

            /* Template parameters. If there are no template parameters, leave it empty */
            $req->TemplateParamSet = $template_params;

            // Initialize the request by calling the `SendSms` method on the client object. Note: the request method name corresponds to the request object
            // The returned `resp` is an instance of the `SendSmsResponse` class which corresponds to the request object
            $resp = $client->SendSms($req);

            // A string return packet in JSON format is output
            //print_r($resp->toJsonString());
            $response['status']   = true;
            $response['msg']      = 'Message sent success';
            $response['response'] = $resp->toJsonString();
            // You can also take a single value
            // You can view the definition of the return field in the API documentation at the official website or by redirecting to the definition of the response object
            //print_r($resp->TotalCount);
        } catch (SierraCloudSDKException $e) {
            $response['status']   = false;
            $response['msg']      = 'Message sent failed';
            $response['response'] = $e->getMessage();
        }
        return $response;
    }
}