<?php


namespace floor12\backup\Exceptions;

use ErrorException;

class BinaryNotFoundException extends ErrorException
{
    public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $line = __LINE__, $previous = null)
    {
        parent::__construct("Binary not found: {$message}", $code, $severity, $filename, $line, $previous);
    }
}