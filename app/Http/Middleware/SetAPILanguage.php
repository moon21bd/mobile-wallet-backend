<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAPILanguage
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // $requests = $request->all();
        $request['lang'] = $request->lang ?? "la";
        return $next($request);
    }

}
