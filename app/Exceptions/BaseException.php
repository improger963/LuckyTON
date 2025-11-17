<?php

namespace App\Exceptions;

use Exception;

abstract class BaseException extends Exception
{
    protected $data = [];

    public function __construct(string $message = "", int $code = 0, array $data = [], Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData(): array
    {
        return $this->data;
    }
}