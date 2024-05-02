<?php

namespace App\Presenter\Http\Controllers;

use OpenApi\Attributes\Info;
use OpenApi\Attributes\SecurityScheme;
use OpenApi\Attributes\Server;

#[
    Server(url: 'http://localhost:8000/api'),
    Info(version: '0.1', title: 'Rocket Eventos'),
    SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer')
]
abstract class Controller
{
    //
}
