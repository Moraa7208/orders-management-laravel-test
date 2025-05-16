<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    /**
     * The error details.
     *
     * @var array
     */
    protected $errors;

    /**
     * Create a new insufficient stock exception instance.
     *
     * @param string $message
     * @param array $errors
     * @return void
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Get the error details.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}