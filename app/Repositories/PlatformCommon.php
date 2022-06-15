<?php

namespace App\Repositories;

use App\Models\WorkFlowPlan;
use Illuminate\Support\Facades\Schema;

class PlatformCommon
{

    /**
     * @return array
     */
    public static function getPermissionField($httpPath)
    {
        $routes = collect(\Route::getRoutes())->map(function ($route) use ($httpPath) {
            return [preg_replace("({[a-zA-Z0-9]+})", "*", $route->uri()) => $route->getActionName()];
        })->reject(function ($route) {
            // dd(array_keys($route));
            // return !Str::contains(array_keys($route)[0], ['admin']);
        });

        $httpPath = 'admin/' . ltrim($httpPath, '/');
        $column = [];
        foreach ($routes as $key => $val) {
            if (isset($val[$httpPath])) {
                $controller = explode('@', $val[$httpPath])[0];
                $controllerObj = new $controller;
                if (!method_exists($controllerObj, 'getUseTableName')) {
                    break;
                }
                $column = self::getTableColumnList($controllerObj->getUseTableName());
                break;
            }
        }
        return $column;
    }
	
	/**
     * @return array
     */
    public static function getPermissionFieldByModelName($modelName)
    {
        $column = [];
		if(!$modelName){
			return $column;
		}
        $controller = 'App\Admin\Controllers\\'.$modelName.'Controller';
		//dd($controller);
		$controllerObj = new $controller;
		if (!method_exists($controllerObj, 'getUseTableName')) {
			return $column;
		}else{
			$column = self::getTableColumnList($controllerObj->getUseTableName());
			return $column;
		}		
    }

    public static function getTableColumnList($tableNames): array
    {
        $tableNames = explode(',', $tableNames);
        foreach ($tableNames as $key => $tableName) {
            $columns = Schema::getColumnListing($tableName);
            foreach ($columns as $clmn) {
                $column[] =  ($key > 0) ? $tableName . '.' . $clmn : $clmn;
            }
        }
        return $column;
    }

    public static function getWorkflow($workflowPlanId, $optionPair, $requestField = [])
    {

        $workflow = WorkFlowPlan::find($workflowPlanId);
        $workFlowModel =  '\App\Models\\' . $workflow->model_name;
        $modelObj = new $workFlowModel;
        $tableName = $modelObj->getTable();
        $query = 'select ' . $optionPair . ' from ' . $tableName;
        $srcQuery = array();

        $conditions = '';
        $numOfCondition = 0;
        foreach ($workflow->dropdown_condition as $key => $condition) {
            $conditionValue = is_array($requestField) ? $requestField[$numOfCondition] : 'NA';
            if (isset($condition['condition_value'])) {
                $condition['condition_value'] = ($condition['condition_op'] == 'like') ? "'%" . $condition['condition_value'] . "%'" : $condition['condition_value'];
            } else {
                $condition['condition_value'] = '?';
                $requestField[$numOfCondition] = ($condition['condition_op'] == 'like') ? "%" . $conditionValue . "%" : $conditionValue;
            }
            $conditions .= ' ' . $condition['condition_field'] . ' ' . $condition['condition_op'] . ' ' . $condition['condition_value'] . ' ';
            $conditions .= ($condition['joining_rules'] == 'NA') ? 'AND' : $condition['joining_rules'];
            $numOfCondition++;
        }
        $conditions = rtrim(rtrim(rtrim($conditions, 'AND'), 'OR'), ' ');
        $conditions = ($conditions != '') ? ' where' . $conditions : $conditions;

        $optionPairs = explode(',', $optionPair);
        if (count($optionPairs) == 2) {
            $pluck = $optionPairs;
        } else {
            $pluck[0] = $optionPair;
            $pluck[1] = $optionPair;
        }

        $srcQuery['query'] = $query . $conditions;
        $srcQuery['conditions'] = $conditions;
        $srcQuery['inputarray'] = $requestField;
        $srcQuery['pluck'] = $pluck;

        return $srcQuery;
    }
}
