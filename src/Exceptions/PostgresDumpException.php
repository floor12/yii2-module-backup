<?php


namespace floor12\backup\Exceptions;


class PostgresDumpException extends \ErrorException
{
    public $message = 'PG_DUMP throws an error.';
}
