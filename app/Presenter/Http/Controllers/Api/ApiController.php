<?php

namespace App\Presenter\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController
{
    protected function responseOk(mixed $payload = null): JsonResponse
    {
        return response()->json($payload, Response::HTTP_OK);
    }

    protected function responseCreated(mixed $payload = null): JsonResponse
    {
        return response()->json($payload, Response::HTTP_CREATED);
    }

    protected function responseBadRequest(array $payload = []): JsonResponse
    {
        return response()->json($payload, Response::HTTP_BAD_REQUEST);
    }
}