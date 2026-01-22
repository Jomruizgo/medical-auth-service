<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $body = [];
    private array $headers = [];

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($this->body, JSON_UNESCAPED_UNICODE);
    }

    public static function success(array $data, int $code = 200): self
    {
        $response = new self();
        $response->setStatusCode($code);
        $response->setBody([
            'success' => true,
            'data' => $data
        ]);
        return $response;
    }

    public static function error(string $message, int $code = 400, ?array $errors = null): self
    {
        $response = new self();
        $response->setStatusCode($code);

        $body = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        $response->setBody($body);
        return $response;
    }

    public static function created(array $data): self
    {
        return self::success($data, 201);
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return self::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::error($message, 401);
    }

    public static function conflict(string $message): self
    {
        return self::error($message, 409);
    }

    public static function validationError(array $errors): self
    {
        return self::error('Validation failed', 400, $errors);
    }
}
