<?php

namespace App\Presenter\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SlugValidationRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = preg_match("/^([A-Za-z0-9]+)(-[A-Za-z0-9]+)*$/is", $value);
        $resultBoolean = (bool) $result;

        if ($resultBoolean === false) {
            $fail('The :attribute must be a slug.');
        }
    }
}
