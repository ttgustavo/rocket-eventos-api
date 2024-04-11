<?php

namespace App\Presenter\Http\Controllers\Auth;

use App\Domain\Error\RegisterError;
use App\Domain\Usecase\RegisterUsecase;
use App\Presenter\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthRegisterController extends Controller
{
    private $usecase;

    public function __construct(RegisterUsecase $usecase)
    {
        $this->usecase = $usecase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $json = json_decode($content, true);
        
        $this->trim($json, ['password']);
        
        $validator = Validator::make(
            $json,
            [
                'name' => 'required|min:3|max:150',
                'email' => 'required|email:rfc,dns',
                'password'=> 'required|min:8|max:100',
            ]
        );
        if ($validator->fails()) {
            $payload = ['code' => 0];
            return response()->json($payload, Response::HTTP_BAD_REQUEST);
        }
        
        $body = (object) $validator->validated();
        $user = $this->usecase->__invoke($body->name, $body->email, $body->password);

        if ($user instanceof RegisterError) {
            $payload = [
                'code' => 1,
                'message' => 'E-mail already in use.'
            ];
            return response()->json($payload, Response::HTTP_BAD_REQUEST);
        }

        return response()->json($user, Response::HTTP_CREATED);
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
