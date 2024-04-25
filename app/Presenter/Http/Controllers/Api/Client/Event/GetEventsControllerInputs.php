<?php

namespace App\Presenter\Http\Controllers\Api\Client\Event;

use Illuminate\Support\Facades\Validator;

class GetEventsControllerInputs
{
    const PARAM_PAGE = 'page';


    public static function validateParams(array $params): \Illuminate\Validation\Validator
    {
        $rules = [
            self::PARAM_PAGE => 'sometimes|filled|integer|min:1'
        ];

        return Validator::make($params, $rules);
    }
}
