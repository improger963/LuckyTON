<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Return a success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'message' => $message,
                'timestamp' => now()->toISOString(),
            ]
        ], $code);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $code
     * @param array $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = '', int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'timestamp' => now()->toISOString(),
            ]
        ];

        if (!empty($errors)) {
            $response['error']['details'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Legacy success method for backward compatibility
     */
    protected function success($data = null, string $message = '', int $code = 200): JsonResponse
    {
        return $this->successResponse($data, $message, $code);
    }

    /**
     * Legacy error method for backward compatibility
     */
    protected function error(string $message = '', int $code = 400, array $errors = []): JsonResponse
    {
        return $this->errorResponse($message, $code, $errors);
    }

    /**
     * Legacy validation error method for backward compatibility
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->validationErrorResponse($errors, $message);
    }
}