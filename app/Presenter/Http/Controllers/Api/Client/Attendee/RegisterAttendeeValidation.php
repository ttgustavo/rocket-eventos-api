<?php

namespace App\Presenter\Http\Controllers\Api\Client\Attendee;

use Illuminate\Support\Facades\Validator;

class RegisterAttendeeValidation
{
    public static function validateParams(int|string $id): \Illuminate\Validation\Validator
    {
        $inputs = [RegisterAttendeeInputs::PARAM_ID => $id];
        $rules = [
            RegisterAttendeeInputs::PARAM_ID => 'required|integer'
        ];

        return Validator::make($inputs, $rules);
    }
}
