<?php
namespace App\Exceptions\Config;

class ConfigFileNotFoundException extends BaseConfigException
{
    public function __construct(string $filepath)
    {
        parent::__construct("File not found", $filepath);
    }
}
