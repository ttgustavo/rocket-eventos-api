<?php

namespace App\Presenter\Http\Controllers\Api\Auth;

use App\Domain\Repository\UserRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthRegisterController extends ApiController
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $json = json_decode($content, true);

        $this->trim($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = $this->validateInputs($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $json = $validator->valid();
        $name = $json[AuthControllerInputs::FIELD_NAME];
        $email = $json[AuthControllerInputs::FIELD_EMAIL];
        $password = $json[AuthControllerInputs::FIELD_PASSWORD];

        $hasEmailRegistered = $this->repository->hasEmailRegistered($email);
        if ($hasEmailRegistered) return parent::responseBadRequest(['code' => 1]);

        $user = $this->repository->insert($name, $email, $password);
        return parent::responseCreated($user);
    }

    private function trim(array &$array, array $ignore = []): void
    {
        foreach ($array as $key => $value) {
            if (is_string($value) === false) continue;
            if (in_array($key, $ignore)) continue;
            $array[$key] = trim($value);
        }
    }

    private function validateInputs(array $inputs): \Illuminate\Validation\Validator
    {
        $rules = [
            AuthControllerInputs::FIELD_NAME => 'required|min:3|max:150',
            AuthControllerInputs::FIELD_EMAIL => 'required|email:rfc,dns',
            AuthControllerInputs::FIELD_PASSWORD => 'required|min:8|max:100'
        ];

        return Validator::make($inputs, $rules);
    }
}
