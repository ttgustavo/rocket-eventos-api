<?php

namespace App\Presenter;

class ArrayUtil
{
    public static function trimValues(array &$array, array $ignoreKeys): void
    {
        foreach ($array as $key => $value) {
            if (is_string($value) === false) continue;
            if (in_array($key, $ignoreKeys)) continue;

            $array[$key] = trim($value);
        }
    }
}
