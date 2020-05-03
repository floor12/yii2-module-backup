<?php


namespace floor12\backup\Exceptions;


class DsnParseException extends \Exception
{
    public $message = 'Database dsn not parsed properly.';

}
