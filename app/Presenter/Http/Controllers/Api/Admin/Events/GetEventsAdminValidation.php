<?php

namespace App\Presenter\Http\Controllers\Api\Admin\Events;

use Illuminate\Support\Facades\Validator;

class GetEventsAdminValidation
{
    private const RULES = [
        GetEventsAdminInputs::PARAM_PAGE => 'sometimes|required|integer|numeric|min:1'
    ];

    public static function validate(array $input): \Illuminate\Validation\Validator
    {
        return Validator::make($input, self::RULES);
    }
}
