<?php

namespace App\Presenter\Http\Controllers\Api;

use App\Presenter\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    protected function responseOk(mixed $payload = null): JsonResponse
    {
        return response()->json($payload, Response::HTTP_OK);
    }

    protected function responseOkJson(string $json): JsonResponse
    {
        return JsonResponse::fromJsonString($json, Response::HTTP_OK);
    }

    protected function responseNoContent(): JsonResponse
    {
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    protected function responseCreated(mixed $payload = null): JsonResponse
    {
        return response()->json($payload, Response::HTTP_CREATED);
    }

    protected function responseBadRequest(array $payload = []): JsonResponse
    {
        return response()->json($payload, Response::HTTP_BAD_REQUEST);
    }

    protected function responseForbidden(array $payload = null): JsonResponse
    {
        return response()->json($payload, Response::HTTP_FORBIDDEN);
    }
}
