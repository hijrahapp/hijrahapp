<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Hijrah App API",
 *     version="1.0.0",
 *     description="API documentation for Hijrah App"
 * )
 *
 * @OA\SecurityScheme(
 *      type="http",
 *      description="Use a bearer token to access this endpoint",
 *      name="Authorization",
 *      in="header",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      securityScheme="bearerAuth",
 *  )
 */
class SwaggerInfo {}
