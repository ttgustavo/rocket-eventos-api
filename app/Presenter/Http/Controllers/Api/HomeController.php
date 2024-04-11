<?php

namespace App\Presenter\Http\Controllers\Api;

use Illuminate\Http\Response;

class HomeController extends ApiController
{
    public function __invoke(): Response
    {
        return response('Hello world!', 200);
    }
}
