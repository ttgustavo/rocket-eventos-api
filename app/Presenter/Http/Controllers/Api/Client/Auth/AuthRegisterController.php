<?php

namespace App\Presenter\Http\Controllers\Api\Client\Auth;

use App\Domain\Repository\UserRepository;
use App\Presenter\ArrayUtil;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        ArrayUtil::trimValues($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = AuthValidation::validateRegister($json);
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
}