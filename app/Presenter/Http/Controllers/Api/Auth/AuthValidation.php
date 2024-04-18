<?php

namespace App\Presenter\Http\Controllers\Api\Auth;

use Illuminate\Support\Facades\Validator;

class AuthValidation
{
    public static function validateRegister(array $inputs): \Illuminate\Validation\Validator
    {
        $rules = [
            AuthControllerInputs::FIELD_NAME => 'required|min:3|max:150',
            AuthControllerInputs::FIELD_EMAIL => 'required|email:rfc,dns',
            AuthControllerInputs::FIELD_PASSWORD => 'required|min:8|max:100'
        ];
        return Validator::make($inputs, $rules);
    }

    public static function validateLogin(array $inputs): \Illuminate\Validation\Validator
    {
        $rules = [
            AuthControllerInputs::FIELD_EMAIL => 'required|email:rfc,dns',
            AuthControllerInputs::FIELD_PASSWORD => 'required|min:1|max:100'
        ];
        return Validator::make($inputs, $rules);
    }
}
