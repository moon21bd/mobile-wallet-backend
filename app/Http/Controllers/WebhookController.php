<?php

namespace App\Http\Controllers;

use App\Models\ProductForm;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use App\Repositories\EWalletLimitConfig;

class WebhookController extends Controller
{
    private $_api_token;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Read at construction time rather than as a property default:
        // a property initializer cannot call a function, and the token must
        // come from the environment rather than the repository.
        $this->_api_token = config('services.webhook.api_token');
    }

    public function saveData(Request $request, $fromName)
    {
        $postData = $request->all();
        //dd($postData);
        $apiToken = !empty($postData['api_token']) ? $postData['api_token'] : '';

        if (!empty($apiToken)) {
            $userAuthData = DB::table('admin_users')
                              ->where('api_token', $apiToken)
                              ->first()
            ;
            if (!empty($userAuthData)) {
                $productForm = ProductForm::where('name', $fromName)
                                          ->first()
                ;
                if (!empty($productForm)) {
                    $tableName        = $productForm->table_name;
                    $tableSchemaInfo  = \Schema::getColumnListing($tableName);
                    $tableColumnCount = count($tableSchemaInfo);
                    unset($tableSchemaInfo[0]);
                    unset($tableSchemaInfo[$tableColumnCount - 2]);
                    unset($tableSchemaInfo[$tableColumnCount - 1]);
                    $postData['created_by'] = $userAuthData->id;
                    $postData['updated_by'] = $userAuthData->id;
                    unset($postData['api_token']);
                    $data               = array_combine($tableSchemaInfo, $postData);
                    $data['created_at'] = now();
                    $data['updated_at'] = now();
                    $status             = DB::table($tableName)
                                            ->insert($postData)
                    ;
                    if ($status == true) {
                        return "Data saved successfully.";
                    } else {
                        return "Failed to save data!";
                    }
                } else {
                    return "Form doesn't exist!";
                }
            } else {
                return "Invalid API token!";
            }
        } else {
            return "API token required!";
        }
    }

    public function createUser(Request $request)
    {
        $postData = $request->all();
        $apiToken = !empty($postData['api_token']) ? $postData['api_token'] : '';

        if (!empty($apiToken)) {
            $userAuthData = DB::table('admin_users')
                              ->where('api_token', $apiToken)
                              ->first()
            ;
            if (!empty($userAuthData)) {
                $checkUserName = DB::table('admin_users')
                                   ->where('username', $postData['username'])
                                   ->first()
                ;
                if (empty($checkUserName)) {
                    $uniqueID = uniqid();
                    $data     = [
                        'username'        => $postData['username'],
                        'name'            => $postData['name'],
                        'password'        => bcrypt($postData['password']),
                        'account_type'    => 'business',
                        'api_token'       => hash('sha256', $uniqueID.Str::random(47)),
                        'organization_id' => $postData['organization_id'],
                        'created_at'      => now(),
                        'updated_at'      => now()
                    ];
                    $id       = DB::table('admin_users')
                                  ->insertGetId($data)
                    ;
                    if ($id) {
                        $userID   = $id;
                        $parentID = $userAuthData->id;
                        DB::select('call update_user_hierarchy(?,?)', array($userID, $parentID));
                        $roleData = [
                            'role_id'    => $postData['role_id'],
                            'user_id'    => $userID,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        DB::table('admin_role_users')
                          ->insert($roleData)
                        ;
                        return "Data saved successfully.";
                    } else {
                        return "failed to save data. Try again later.";
                    }
                } else {
                    return "User already exists!";
                }
            } else {
                return "Invalid API token!";
            }
        } else {
            return "API token required!";
        }
    }

    public function syncLimitConfig(Request $request): JsonResponse
    {
        $api_name = 'syncLimitConfig';
        //validation
        $validator = Validator::make($request->all(), [
            'api_token' => 'required|string',
            'user_id'   => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $status_code = Response::HTTP_BAD_REQUEST;//400;
            $response    = $validator->errors();
            return $this->sendResponse($response, $status_code, $api_name);
        }
        //parmas
        $api_token = $request->api_token;
        $user_id   = $request->user_id;
        //api token validation
        if ($api_token != $this->_api_token) {
            $status_code = Response::HTTP_UNAUTHORIZED;//401;
            $response    = [
                'status' => false,
                'msg'    => 'Api Token Invalid',
                'code'   => $status_code
            ];
            return $this->sendResponse($response, $status_code, $api_name);
        }
        //sync config
        if (!EWalletLimitConfig::copyConfig($user_id)){
            $status_code = Response::HTTP_EXPECTATION_FAILED;//417;
            $response    = [
                'status' => false,
                'msg'    => 'Limit Config copy Failed',
                'code'   => $status_code
            ];
            return $this->sendResponse($response, $status_code, $api_name);
        }

        //final response
        $status_code = Response::HTTP_OK;//200;
        $response    = [
            'status' => true,
            'msg'    => 'Limit Config copy Successfully',
            'code'   => $status_code
        ];
        return $this->sendResponse($response, $status_code, $api_name);
    }

    private function sendResponse($response, $code, $api_name): JsonResponse
    {
        //log for request
        Log::info($api_name.'- Response#'.json_encode($response));
        return response()->json($response, $code);
    }
}
