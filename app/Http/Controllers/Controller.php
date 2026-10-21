<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "rohit-backend",
    description: "API documentation for rohit-backend   "
)]
#[OA\Server(
    url: "/",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctumToken",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Bearer"
)]
abstract class Controller
{
    //
}

