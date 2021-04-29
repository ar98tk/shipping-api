<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if(!auth()->guard('user-api')->check()){
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
