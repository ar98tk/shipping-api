<?php

namespace App\Http\Controllers;

trait ApiResponseTrait{

    public $paginatNumber = 5;

    public function apiResponse($status = 200, $data = null, $message = null){
        $array = [
            'data' => $data,
            'message' => $message,
            'code' => $status,
            ];
        return response($array, $status);
    }

    public function successCode(){
        return [
            200,201,202
        ];
    }

    public function notFoundResponce(){
        return $this->apiResponse(null,'Not found', 404);
    }
}
