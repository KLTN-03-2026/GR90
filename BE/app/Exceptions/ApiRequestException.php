<?php

namespace App\Exceptions;

use Exception;

class ApiRequestException extends Exception
{
    protected mixed $errors;

    public function __construct(string $message = 'Yêu cầu không hợp lệ', mixed $errors = null)
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }
}
