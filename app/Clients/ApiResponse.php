<?php

namespace App\Clients;

use Illuminate\Http\JsonResponse;

class ApiResponse extends JsonResponse
{
    public function __construct($data = null, $code = 200, $message = null, $error = false, $headers = [], $options = 0)
    {
        $responseData = [
            'error' => $error,
            'code' => $code,
            'message' => $message ?? self::getStatusMessage($code),
            'data' => $data,
        ];

        parent::__construct($responseData, $code, $headers, $options);
    }

    private static function getStatusMessage($code)
    {
        switch ($code) {
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 204:
                return 'No Content';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not Found';
            case 422:
                return 'Unprocessable Entity';
            case 500:
                return 'Internal Server Error';
            default:
                return 'Unknown Code';
        }
    }
}
