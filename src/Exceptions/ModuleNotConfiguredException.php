<?php


namespace floor12\backup\Exceptions;


class ModuleNotConfiguredException extends \ErrorException
{
    public $message = 'Configuration not found in app config.';

}
