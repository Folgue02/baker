<?php
namespace App\Exceptions\Config;

/**
 * Thrown when the configuration file holds a data structure different
 * from the configuration's, making it impossible to be deserialized.
 */
class InvalidConfigContentsException extends BaseConfigException
{
    public function __construct(?string $filepath = null, ?\Exception $cause = null)
    {
        parent::__construct(
            "Invalid configuration structure ({$cause->getMessage()})",
            $filepath,
            $cause
        );
    }
}
