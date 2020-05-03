<?php


namespace floor12\backup\Exceptions;


class ConfigurationNotFoundException extends \ErrorException
{
    public $message = 'Requested configuration not found in app config.';

}
