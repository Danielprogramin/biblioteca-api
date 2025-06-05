<?php

namespace App\Traits;

trait ApiResponses
{
    public function ok($message, $data = [])
    {
        return $this->success($message, $data, 200);
    }

    protected function success($message, $data = [], $statusCode = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    protected function error($message, $statusCode, $error = null)
    {
        return response()->json([
            'message' => $message,
            'error' => $error,
            'status' => $statusCode
        ], $statusCode);
    }
}
