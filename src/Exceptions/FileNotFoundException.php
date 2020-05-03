<?php


namespace floor12\backup\Exceptions;


class FileNotFoundException extends \ErrorException
{
    public $message = 'File not found on disk.';

}
