<?php

namespace App\Http\Middleware;

use Encore\Admin\Facades\Admin;
use Closure;

class RoutePermissionInformation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $collection = collect(Admin::user()->roles[0]->permissions);
		
		$currentPath = trim($request->path(), 'admin');		
		$searchpattern = '/(\d+)/i';		
		$replacement = '*';		
		$modifiedPath = preg_replace($searchpattern, $replacement, $currentPath);
		
        $permissoninfos = $collection->whereIn('http_path', [$modifiedPath, '*'])->first();
        //$permissoninfos = $collection->where('http_path', trim($request->path(), 'admin'))->first();
        if (!empty($permissoninfos)) {
            $permissoninfos = $permissoninfos->toArray();
            $routespecificpermission = empty($permissoninfos) ? [] : ['field_list' => empty($permissoninfos['field_list']) ? '' : $permissoninfos['field_list'], 'field_condition' => empty($permissoninfos['field_condition']) ? '' : $permissoninfos['field_condition'], 'action_permission' => empty($permissoninfos['action_permission']) ? '' : $permissoninfos['action_permission']];
        } else {
            $routespecificpermission = [];
        }
        $request->merge(array('routespecificpermission' => $routespecificpermission));
        return $next($request);
    }
}
