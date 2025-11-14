<?php

namespace App\Contracts;

use Illuminate\Http\JsonResponse;

interface ApiResponseHandlerContract
{
    /**
     * Return a success JSON response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse;

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    public function error(string $message = 'Error', int $statusCode = 400, $errors = null): JsonResponse;
}
