<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Exceptions\ConflictException;
use App\Core\Exceptions\UnauthorizedException;
use App\Core\Exceptions\ValidationException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(Request $request, array $params): Response
    {
        try {
            $userResponse = $this->authService->register($request->getBody());

            return Response::created($userResponse->toArray());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (ConflictException $e) {
            return Response::conflict($e->getMessage());
        }
    }

    public function login(Request $request, array $params): Response
    {
        try {
            $loginResponse = $this->authService->login($request->getBody());

            return Response::success($loginResponse->toArray());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (UnauthorizedException $e) {
            return Response::unauthorized($e->getMessage());
        }
    }

    public function refresh(Request $request, array $params): Response
    {
        try {
            $tokenResponse = $this->authService->refresh($request->getBody());

            return Response::success($tokenResponse->toArray());
        } catch (ValidationException $e) {
            return Response::validationError($e->getErrors());
        } catch (UnauthorizedException $e) {
            return Response::unauthorized($e->getMessage());
        }
    }
}
