<?php

namespace App\Presenter\Http\Controllers\Api\Auth;

use App\Infrastructure\Eloquent\Models\User;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthLoginController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        $this->trim($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = $this->validateInputs($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $data = $validator->valid();

        $authenticated = Auth::once($data);
        if ($authenticated === false) return parent::responseBadRequest(['code' => 1]);

        /** @var Authenticatable|User $user */
        $user = Auth::user();
        $token = $user->createToken('user')->plainTextToken;

        return parent::responseOk(['token' => $token]);
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
            AuthControllerInputs::FIELD_EMAIL => 'required|email:rfc,dns',
            AuthControllerInputs::FIELD_PASSWORD => 'required|min:1|max:100'
        ];

        return Validator::make($inputs, $rules);
    }
}
