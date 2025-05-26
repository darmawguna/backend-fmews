<?php

namespace App\Helpers;

class ApiResponse
{
    /**
     * Mengirimkan response sukses dengan data.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'api_version' => 'v1', // bisa diubah sesuai versi API kamu
        ], $statusCode);
    }
}
