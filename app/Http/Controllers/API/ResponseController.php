<?php

namespace App\Http\Controllers\API;


use App\Services\EncryptionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Log;

class ResponseController extends Controller
{
    public function sendResponse($response, $code = 200)
    {
        $request_path = \request()->path();
        //Need a check for exclude or include any request path
        Log::info('App Request Path: '.$request_path);
        if (!in_array($request_path, config('excluded_paths'))) {
            return $this->sendResponseApi($response, $code);
        } else {
            return response()->json($response, $code);
        }
    }

    public function sendResponseForPanel($response, $code = 200)
    {
        $request_path = \request()->path();
        Log::info('Admin Request Path: '.$request_path);
        return response()->json($response, $code);
    }

    /**
     * @param $response
     * @param  int  $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponseApi($response, $code = 200)
    {
        $encryptionService              = new EncryptionService();
        $token              = $encryptionService->getEncryptedData($response);
        $payload['payload'] = $token;
        return response()->json($payload, $code);
    }

    public function sendError($error, $code = 422)
    {
        $response = [
            'errors' => $error,
        ];
        return response()->json($response, $code);
    }

    public function sendError_($error, $code = 422, $message = '')
    {
        $response = [
            'errors'  => $error,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    /**
     * function name: makeResponse
     * return array or object response
     * Created by Raqibul Hasan
     *
     * @param  status,code,message,data
     */
    public function makeResponse(string $status, int $code, $message = '', $data = '', $additionalDataArr = [])
    {
        $objArr = [
            'code'    => $code,
            'status'  => $status,
            'message' => $message,
        ];

        if (!empty($additionalDataArr)) {
            foreach ($additionalDataArr ?? [] as $key => $value) {
                $objArr[$key] = $value;
            }
        }

        /*if (!empty($data)) {
            $objArr['data'] = $data;
        }*/
        $objArr['data'] = $data;
        return $objArr;
    }

    public function validationMsg($language = "en", $messageKey, $asArray = false)
    {
        if ($asArray) {
            return [
                "en" => config("validation-messages.en")[$messageKey],
                "la" => config("validation-messages.la")[$messageKey]
            ];
        } else {
            return config("validation-messages.$language")[$messageKey];
        }
    }

    /**
     * Return a validation error message
     * Created by Raqibul Hasan
     *
     * @return validation error
     */
    protected function phoneValidationErrorMessages($msisdn = 'mobile_no'): array
    {
        return [
            $msisdn.'.required' => $msisdn.' is required.',
            $msisdn.'.numeric'  => 'Please provide only numeric value.',
            $msisdn.'.digits'   => $msisdn.' field must be 11 digits.',
            $msisdn.'.regex'    => $msisdn.' does not match with Bangladeshi operators.',
        ];
    }

    /**
     * Return a validation rules
     * Created by Raqibul Hasan
     *
     * @return validation error
     */
    protected function phoneValidationRules()
    {
        return 'required|numeric';
    }

    protected function makeInvalidParameterDetails(\Illuminate\Support\MessageBag $errors): string
    {
        $message = "";
        foreach ($errors->getMessages() as $key => $value) {
            $message .= " ".$value[0];
        }
        return $message;
    }

    protected function getRefId($input, $user)
    {
        $input['organization_ref_id'] = $user->organization_ref_id;
        $input['user_ref_id']         = $user->user_ref_id;
        $input['role_ref_id']         = $user->role_ref_id;
        $input['department_ref_id']   = $user->department_ref_id;
        $input['created_by']          = $user->id ?? 0;
        $input['updated_by']          = $user->id ?? 0;
        return $input;
    }

}
