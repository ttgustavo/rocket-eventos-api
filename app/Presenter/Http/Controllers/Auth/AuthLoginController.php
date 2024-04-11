<?php

namespace App\Presenter\Http\Controllers\Auth;

use App\Presenter\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthLoginController extends Controller
{
    const VALIDATOR_RULES = [
        'email' => 'required|email:rfc,dns',
        'password'=> 'required|min:1|max:100',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        $this->trim($json, ['password']);

        $validator = Validator::make($json, self::VALIDATOR_RULES);
        if ($validator->fails()) return $this->responseFailure(0);

        $data = $validator->validated();
        $isAuthenticated = Auth::once($data);

        if ($isAuthenticated === false) return $this->responseFailure(1);

        $token = $request->user()->createToken('user')->plainTextToken;
        return $this->responseSuccess($token);
    }

    private function responseSuccess(string $token): JsonResponse
    {
        $payload = ['token' => $token];
        return response()->json($payload, Response::HTTP_OK);
    }

    private function responseFailure(int $code): JsonResponse
    {
        $payload = ['code' => $code];
        return response()->json($payload, Response::HTTP_BAD_REQUEST);
    }

    private function trim(array &$array, array $ignore = []): void
    {
        foreach ($array as $key => $value) {
            if (is_string($value) === false) continue;
            if (in_array($key, $ignore)) continue;
            $array[$key] = trim($value);
        }
    }
}
