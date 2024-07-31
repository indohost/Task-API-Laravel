<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $data = []
    ): JsonResponse {

        $response = [
            'status' => 'OK',
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $pagination = [
                'current_page' => $data->currentPage(),
                'data' => $data->items(),
                'first_page_url' => $data->url(1),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'last_page_url' => $data->url($data->lastPage()),
                'links' => $data->linkCollection()->toArray(),
                'next_page_url' => $data->nextPageUrl(),
                'path' => $data->path(),
                'per_page' => $data->perPage(),
                'prev_page_url' => $data->previousPageUrl(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ];

            $response['pagination'] = $pagination;

            $data = $data->items();
        } else {
            if (!empty($data)) {
                $response['data'] = $data;
            }
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
        string $message = 'Validation errors occurred',
        array $errors = []
    ): JsonResponse {
        $status = HTTPCode::HTTP_UNPROCESSABLE_ENTITY;

        return $this->failedResponse($message, $status, $errors);
    }
}
