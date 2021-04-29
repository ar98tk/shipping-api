<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DriverMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->guard('driver-api')->check()){
            return $this->apiResponce(401, null, 'The authentication failed.');
        }
        return $next($request);
    }

    private function apiResponce($status = 200, $data = null, $message = null)
    {
        $array = [
            'data' => $data,
            'message' => $message,
            'code' => $status,
        ];
        return response($array, $status);
    }
}
