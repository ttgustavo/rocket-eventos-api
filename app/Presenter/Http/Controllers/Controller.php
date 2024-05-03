<?php

namespace App\Presenter\Http\Controllers;

use OpenApi\Attributes\Info;
use OpenApi\Attributes\Server;

#[
    Server(url: 'http://localhost:8000/api'),
    Info(version: '0.1', title: 'Rocket Eventos'),
]
abstract class Controller
{
}
