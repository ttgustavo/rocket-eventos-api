<?php

namespace App\Presenter\Http\Controllers\Api\Client\User;

use App\Domain\Repository\UserRepository;
use App\Presenter\ArrayUtil;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdateUserController extends ApiController
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

        $ignoreTrim = [UpdateUserInputs::FIELD_OLD_PASSWORD, UpdateUserInputs::FIELD_NEW_PASSWORD];
        ArrayUtil::trimValues($json, $ignoreTrim);

        $validator = UpdateUserValidation::validate($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $json = $validator->valid();
        $name = null;
        $email = null;
        $oldPassword = null;
        $newPassword = null;

        if (key_exists(UpdateUserInputs::FIELD_NAME, $json)) {
            $name = $json[UpdateUserInputs::FIELD_NAME];
        }
        if (key_exists(UpdateUserInputs::FIELD_EMAIL, $json)) {
            $email = $json[UpdateUserInputs::FIELD_EMAIL];
        }
        if (key_exists(UpdateUserInputs::FIELD_NEW_PASSWORD, $json)) {
            $oldPassword = $json[UpdateUserInputs::FIELD_OLD_PASSWORD];
            $newPassword = $json[UpdateUserInputs::FIELD_NEW_PASSWORD];
        }

        $user = Auth::user();

        $isNotUpdatingPassword = is_null($oldPassword) && is_null($newPassword);

        if ($isNotUpdatingPassword) {
            $user = $this->repository->update($user->id, $name, $email, $newPassword);
            return parent::responseOk($user);
        }

        $savedPassword = $this->repository->getPasswordFromUser($user->id);
        $isOldPasswordSame = Hash::check($oldPassword, $savedPassword);
        if ($isOldPasswordSame === false) return parent::responseBadRequest(['code' => 1]);

        $user = $this->repository->update($user->id, $name, $email, $newPassword);
        return parent::responseOk($user);
    }
}
