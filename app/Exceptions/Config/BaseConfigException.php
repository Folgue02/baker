<?php
namespace App\Exceptions\Config;

use Exception;

abstract class BaseConfigException extends Exception
{
    public function __construct(
        string $message,
        public ?string $configFilepath = null,
        public ?\Exception $cause = null
    ) {
        $message = $configFilepath
            ? $message . " (configuration file path: '$configFilepath')"
            : $message;

        parent::__construct($message, previous: $cause);
    }
}
