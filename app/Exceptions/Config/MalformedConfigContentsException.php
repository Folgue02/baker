<?php
namespace App\Exceptions\Config;

class MalformedConfigContentsException extends BaseConfigException
{
    public function __construct(?string $filepath = null)
    {
        parent::__construct("Malformed configuration file (invalid json)", $filepath);
    }
}
