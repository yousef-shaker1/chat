<?php

namespace App\Traits;

trait HttpResponses
{
    public function successResponse($data = [], $message = "Success", $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse($message = "Not Found", $code = 404)
    {
        return response()->json([
            'message' => $message,
        ], $code);
    }
}
