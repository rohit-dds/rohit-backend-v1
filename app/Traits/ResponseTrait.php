<?php

namespace App\Traits;

trait ResponseTrait
{
    public function successResponse($data = [], $message,$status=200){
        return response()->json([
            "success"=>true,
            "message"=>$message,
            "data"=>$data,
            "status"=>$status,
        ]);
    }

    public function errorResponse($data = [], $message,$status=404){
        return response()->json([
            "success"=>false,
            "data"=>$data,
            "status"=>$status,
        ]);
    }

    public function validationErrorResponse($data = [], $message = 'Validation failed', $status = 422)  
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'status' => $status,
        ], $status);
    }

}
