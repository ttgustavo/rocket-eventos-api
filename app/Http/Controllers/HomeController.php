<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return response('Hello world!', 200);
    }
}
