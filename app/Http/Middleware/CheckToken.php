<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        $mobile = Cache::get($token);
        if (!$mobile) {
            $ret = array(
                'code' => 202,
                'data' => '',
                'msg' => '登录已过期，请重新登录'
            );
            return response()->json($ret);
        } else {
            return $next($request);
        }
    }
}
