<?php

namespace App\Presenter\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class DateTimeAfterNowRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $dateTimeNow = Carbon::now();
        $dateTimeSpecified = Carbon::parse($value);

        if ($dateTimeSpecified->isBefore($dateTimeNow)) {
            $fail('The :attribute must be future.');
        }
    }
}