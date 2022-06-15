<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class RateHandler
{

    public static function getRateType()
    {
        try {
            $client = new Client();
            $response = $client->get(env('RATE_BASE_URL') . '/rating-type');
            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngine::getRateTypeResponse: ' . json_encode($result));
            if ($result['status'] == 200) {
                return $result['data'];
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public function createRateType($name, $equation, $order, $createdBy)
    {
        try {
            $client = new Client();
            $data = [
                "name" => $name,
                "createdBy" => $createdBy,
                "equation" => $equation,
                "order" => $order,
            ];
            Log::info('RateEngineRequest::createRateTypeRequest: ' . json_encode($data));
            $response = $client->post(env('RATE_BASE_URL') . '/rating-type', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngineResponse::createRateTypeResponse: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 0;
            } else {
                return $result['data'][0]['id'];
            }


        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }

    }

    public function updateRateType($refID, $name, $equation, $order, $updatedBy)
    {
        try {
            $client = new Client();
            $data = [
                "id" => $refID,
                /*"name" => $name,*/
                "updatedBy" => $updatedBy,
                "equation" => $equation,
                "order" => $order,
            ];
            Log::info('RateEngineRequest::updateRateTypeRequest: ' . json_encode($data));
            $response = $client->put(env('RATE_BASE_URL') . '/rating-type', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngineResponse::updateRateTypeResponse: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 0;
            } else {
                return $result['data'][0]['id'];
            }


        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }

    }

    public static function getRateConditionNameConfig()
    {
        try {
            $client = new Client();
            //echo env('COMMISSION_BASE_URL').'/commission?Amount='.$amount.'&'.$configName.'='.$value;
            $response = $client->get(env('RATE_BASE_URL') . '/rating-condition-name');
            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngineResponse::getRateConditionNameConfig: ' . json_encode($result));
            if ($result['status'] == 200) {
                return $result['data'];
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public function createRateNameConfig($name, $type, $validationSchema, $createdBy)
    {
        try {
            $preparedValidationSchema = [
                "type" => $type
            ];

            if (!empty($validationSchema)) {
                foreach ($validationSchema as $schema) {
                    $preparedValidationSchema[$schema['key']] = $schema['value'];
                }
            }

            $client = new Client();
            $data = [
                "name" => $name,
                "createdBy" => $createdBy,
                "validationSchema" => $preparedValidationSchema
            ];
            Log::info('RateEngineRequest::createRateNameConfig: ' . json_encode($data));
            $response = $client->post(env('RATE_BASE_URL') . '/rating-condition-name', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngineResponse::createRateNameConfig: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 0;
            } else {
                return $result['data'][0]['id'];
            }


        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }

    }

    public function updateRateNameConfig($refID, $name, $type, $validationSchema, $createdBy)
    {
        try {
            $preparedValidationSchema = [
                "type" => $type
            ];

            if (!empty($validationSchema)) {
                foreach ($validationSchema as $schema) {
                    $preparedValidationSchema[$schema['key']] = $schema['value'];
                }
            }

            $client = new Client();
            $data = [
                "id" => $refID,
                "name" => $name,
                "updatedBy" => $createdBy,
                "validationSchema" => $preparedValidationSchema
            ];
            Log::info('RateEngine::updateRateNameConfigRequest: ' . json_encode($data));
            $response = $client->put(env('RATE_BASE_URL') . '/rating-condition-name', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngine::updateRateNameConfigRequest: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 0;
            } else {
                return $result['data'][0]['id'];
            }


        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 0;
        }

    }

    public function createRateConfiguration($name, $rateTypeId,
                                            $rate, $currency,
                                            $conditions, $createdBy, $activeStatus)
    {
        try {
            $preparedConditions = [];

            $key = 0;
            foreach ($conditions as $condition) {
                if ($condition['_remove_'] == 1) {
                    continue;
                }
                $preparedConditions[$key]['name'] = $condition['rate_condition_ref_id'];
                $preparedConditions[$key]['op'] = $condition['op'];
                $preparedConditions[$key]['value'] = $condition['value'];
                if (!empty($condition['rating_config_ref_id'])) {
                    $preparedConditions[$key]['conditionId'] = $condition['rating_config_ref_id'];
                }
                ++$key;
            }

            $client = new Client();
            $data = [
                "name" => $name,
                "ratingTypeId" => $rateTypeId,
                "rate" => $rate,
                "currency" => $currency,
                "createdBy" => $createdBy,
                "conditions" => $preparedConditions
            ];
            Log::info('RateEngine::createRateConfigurationRequest: ' . json_encode($data));

            $response = $client->post(env('RATE_BASE_URL') . '/rating-config', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngine::createRateConfigurationResponse: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 'error';
            } else {
                return $result['data'][0];
            }
        } catch (ConnectException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 'error';
        } catch (GuzzleException $e) {
            admin_error("Rate API Connection Failed!", $e->getMessage());
            return 'error';
        }
    }

    public function updateRateConfiguration($refId, $name, $rateTypeId,
                                            $rate, $currency,
                                            $conditions, $createdBy, $status)
    {
        try {

            $preparedConditions = [];

            $key = 0;
            foreach ($conditions as $condition) {
                $preparedConditions[$key]['name'] = $condition['rate_condition_ref_id'];
                $preparedConditions[$key]['op'] = $condition['op'];
                $preparedConditions[$key]['value'] = $condition['value'];
                if (!empty($condition['ref_id'])) {
                    $preparedConditions[$key]['conditionId'] = $condition['ref_id'];
                    if ($condition['_remove_'] == 1) {
                        $preparedConditions[$key]['status'] = 0;
                    }
                }
                ++$key;
            }


            $client = new Client();
            $activeStatus = 0;
            if ($status == 'on') {
                $activeStatus = 1;
            }
            $data = [
                "id" => $refId,
                "name" => $name,
                "ratingTypeId" => $rateTypeId,
                "rate" => $rate,
                "currency" => $currency,
                "conditions" => $preparedConditions,
                "updatedBy" => $createdBy,
                "status" => $activeStatus
            ];
            Log::info('RateEngine::updateRateConfigurationRequest: ' . json_encode($data));
            $response = $client->put(env('RATE_BASE_URL') . '/rating-config', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('RateEngine::updateRateConfigurationResponse: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 'error';
            } else {
                return $result['data'][0];
            }
        } catch (ConnectException $e) {
            admin_error("RateEngine API Connection Failed!", $e->getMessage());
            return 'error';
        } catch (GuzzleException $e) {
            admin_error("RateEngine API Connection Failed!", $e->getMessage());
            return 'error';
        }
    }

    public static function getConditionNameConfig()
    {
        try {
            $client = new Client();
            //echo env('COMMISSION_BASE_URL').'/commission?Amount='.$amount.'&'.$configName.'='.$value;
            $response = $client->get(env('COMMISSION_BASE_URL') . '/condition-name-config');
            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('Commission::getConditionNameConfigResponse: ' . json_encode($result));
            if ($result['status'] == 200) {
                return $result['data'];
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public function getCommissionKeys($conditions)
    {
        $responseArray = [];
        $key = 0;
        foreach ($conditions as $condition) {
            $responseArray[$key] = $condition['commission_name_config_id'];
            ++$key;
        }

        return $responseArray;
    }

    public function getCommissionKeyValue($conditions, $valueArray)
    {
        $responseArray = [];
        $key = 0;
        foreach ($conditions as $condition) {
            $responseArray[$condition['commission_name_config_id']] = $valueArray[$key];
            ++$key;
        }

        return $responseArray;
    }

    public function getCommission($amount, $commissionArray)
    {
        $queryParam = http_build_query($commissionArray);
        try {
            $client = new Client();
            /*echo env('COMMISSION_BASE_URL').'/commission?Amount='.$amount.'&'.$queryParam;
            exit;*/
            $response = $client->get(env('COMMISSION_BASE_URL') . '/commission?Amount=' . $amount . '&' . $queryParam);
            $result = json_decode($response->getBody()->getContents(), 1);
            if ($result['status'] == 200) {
                return ['totalCommission' => $result['data'][0]['totalCommission'], 'applicablePeriod' => $result['data'][0]['applicablePeriod']];
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public function postCommissionLogData($amount, $commissionArray, $salesInfo, $applicablePeriod)
    {
        $commissionLogObj = new CommissionLog();
        $commissionLogObj->core_account_id = $salesInfo['accountId'];
        $commissionLogObj->amount = $amount;
        $commissionLogObj->product_id = $salesInfo['productId'];
        $commissionLogObj->product_quantity = $salesInfo['productQuantity'];
        $commissionLogObj->user_id = !empty($salesInfo['userId']) ? $salesInfo['userId'] : '';
        $commissionLogObj->customer_id = !empty($salesInfo['customerId']) ? $salesInfo['customerId'] : '';
        $commissionLogObj->user_role_id = !empty($salesInfo['userRoleId']) ? $salesInfo['userRoleId'] : '';
        $commissionLogObj->customer_role_id = !empty($salesInfo['customerRoleId']) ? $salesInfo['customerRoleId'] : '';
        $commissionLogObj->user_org_id = !empty($salesInfo['userOrgId']) ? $salesInfo['userOrgId'] : '';
        $commissionLogObj->customer_org_id = !empty($salesInfo['customerOrgId']) ? $salesInfo['customerOrgId'] : '';
        $commissionLogObj->created_by = !empty($salesInfo['createdBy']) ? $salesInfo['createdBy'] : '';
        if ($commissionLogObj->save()) {
            $clientRefId = $commissionLogObj->id;
        } else {
            $clientRefId = uniqid();
        }
        $postData = $commissionArray;
        $postData['Amount'] = $amount;
        $postData['sendAcctTrx'] = ($applicablePeriod == 'After-Commission-Period') ? 1 : 0;
        $postData['salesInfo'] = $salesInfo;
        $postData['salesInfo']["clientRefId"] = (string)$clientRefId;

        try {
            Log::info('Commission::PostCommissionRequest: ' . json_encode($postData));
            $client = new Client();
            $data = $postData;

            $response = $client->post(env('COMMISSION_BASE_URL') . '/commission', [
                RequestOptions::JSON => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), 1);
            Log::info('Commission::PostCommissionResponse: ' . json_encode($result));
            if ($result['status'] != 200) {
                admin_error($result['details']);
                return 'error';
            } else {
                return ['totalCommission' => $result['data'][0]['totalCommission'], 'applicablePeriod' => $result['data'][0]['applicablePeriod']];
            }
        } catch (ConnectException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        } catch (GuzzleException $e) {
            admin_error("Commission API Connection Failed!", $e->getMessage());
            return 0;
        }
    }

    public static function getRate($rateArray, $totalOnly = true)
    {
        $queryParam = http_build_query($rateArray);
        $url = env('CBS_PRODUCT_RATING_BASE_URL') . '/rating?' . $queryParam;
        Log::info('RateEngine::GetRateRequest: ' . $url);

        try {
            $client = new Client();
            $response = $client->get($url, ['connect_timeout' => 10]);
            $result = json_decode($response->getBody()->getContents(), 1, 512, JSON_THROW_ON_ERROR);

            Log::info('RateEngine::GetRateResponse: ' . json_encode($result, JSON_THROW_ON_ERROR));

            if ((int)$result['status'] === 200) {
                if ($totalOnly == true) {
                    return ['total' => $result['data']['totalPrice'], 'currency' => $result['data']['currency']];
                } else {
                    return $result['data'];
                }
            }

            return 'error';
        } catch (ConnectException $e) {
            admin_error("Rating API Connection Failed!", $e->getMessage());
            return 'error';
        } catch (GuzzleException $e) {
            admin_error("Rating API Connection Failed!", $e->getMessage());
            return 'error';
        }
    }

}
