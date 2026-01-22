<?php

declare(strict_types=1);

use App\Controllers\AuthController;

$router->post('/api/auth/register', [AuthController::class, 'register']);
