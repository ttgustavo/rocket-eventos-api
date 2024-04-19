<?php

namespace App\Presenter\Http\Controllers\Api\Client\User;

use Illuminate\Support\Facades\Validator;

class UpdateUserValidation
{
    public static function validate(array $inputs): \Illuminate\Validation\Validator
    {
        $fieldOldPassword = UpdateUserInputs::FIELD_OLD_PASSWORD;
        $fieldNewPassword = UpdateUserInputs::FIELD_NEW_PASSWORD;

        $rules = [
            UpdateUserInputs::FIELD_NAME => 'sometimes|filled|min:2|max:150',
            UpdateUserInputs::FIELD_EMAIL => 'sometimes|filled|email:rfc,dns',
            UpdateUserInputs::FIELD_OLD_PASSWORD => "required_with:$fieldNewPassword|filled|min:8|max:100",
            UpdateUserInputs::FIELD_NEW_PASSWORD => "required_with:$fieldOldPassword|filled|min:8|max:100",
        ];
        return Validator::make($inputs, $rules);
    }
}
