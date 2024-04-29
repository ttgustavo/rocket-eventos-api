<?php

namespace App\Presenter\Http\Controllers\Api\Client\Auth;

use App\Domain\Repository\UserRepository;
use App\Infrastructure\Eloquent\Models\User;
use App\Presenter\ArrayUtil;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthLoginController extends ApiController
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        ArrayUtil::trimValues($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = AuthValidation::validateLogin($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $data = $validator->valid();

        $authenticated = Auth::once($data);
        if ($authenticated === false) return parent::responseBadRequest(['code' => 1]);

        $isAccountBanned = $this->repository->isBanned($data['email']);
        if ($isAccountBanned) return parent::responseForbidden();

        /** @var Authenticatable|User $user */
        $user = Auth::user();
        $token = $user->createToken('user')->plainTextToken;

        return parent::responseOk(['token' => $token]);
    }
}
