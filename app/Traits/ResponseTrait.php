<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

trait ResponseTrait
{
    /**
     * Send a response for authorization.
     *
     * @param string|null $message
     * @param string $token
     * @param mixed $user
     * @return JsonResponse
     */
    protected function authorizationResponse(
        string $message = 'Authorization successful',
        string $token,
        $user
    ): JsonResponse {
        return response()->json([
            'status' => 'OK',
            'message' => $message,
            'authorization' => [
                'type' => 'bearer',
                'token' => $token,
            ],
            'user'    => $user,
        ], HTTPCode::HTTP_OK);
    }

    /**
     * Send a success response.
     *
     * @param string|null $message
     * @param int $status
     * @param mixed $data
     * @return JsonResponse
     */
    protected function successResponse(
        ?string $message,
        int $status = HTTPCode::HTTP_OK,
        array $data = []
    ): JsonResponse {

        $response = [
            'status' => 'OK',
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Send a failed response.
     *
     * @param string $message
     * @param int $status
     * @param mixed|null $errors
     * @return JsonResponse
     */
    protected function failedResponse(
        ?string $message,
        int $status = HTTPCode::HTTP_BAD_REQUEST,
        $errors = null
    ): JsonResponse {
        $response = [
            'status' => 'ERROR',
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Send a response for failed validation.
     *
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return JsonResponse
     */
    protected function validationFailedResponse(
        string $message = 'Validation failed',
        array $errors
    ): JsonResponse {
        $status = HTTPCode::HTTP_UNPROCESSABLE_ENTITY;

        return $this->failedResponse($message, $status, $errors);
    }
}
