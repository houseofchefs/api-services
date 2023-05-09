<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTraits
{
    /**
     * @param $status
     * @param $data
     * @param $msg
     * @param $http_status
     * @return JsonResponse
     */
    protected function successResponse($status, $data, $msg, $http_status = 200): JsonResponse
    {
        return response()->json(array(
            'status' => $status,
            'status_code' => $http_status,
            'data' => $data,
            'msg' => $msg
        ), $http_status);
    }

    protected function validatorErrorResponse($status, $error, $msg, $http_status = 422): JsonResponse
    {
        return response()->json(array(
            'status' => $status,
            'status_code' => $http_status,
            'error' => $error,
            'msg' => $msg
        ), $http_status);
    }

    protected function tokenResponse($status, $user, $token, $msg, $http_status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $msg,
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], $http_status);
    }

    protected function errorResponse($status, $error, $msg, $http_status = 200): JsonResponse
    {
        return response()->json(array(
            'status' => $status,
            'status_code' => $http_status,
            'error' => $error,
            'msg' => $msg
        ), $http_status);
    }
}
