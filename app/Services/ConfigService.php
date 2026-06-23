<?php
namespace App\Services;

use App\Models\Config\BakerConfiguration;
use JsonMapper;
use RuntimeException;

class ConfigService
{
    public const DEFAULT_CONFIG_FILENAME = '.config.baker.json';
    public function readConfiguration(?string $configFilePath = null): BakerConfiguration
    {
        $configFilePath = $configFilePath ?? $this->defaultConfigFilepath();

        $contents = file_get_contents($configFilePath);

        if (!$contents)
            throw new RuntimeException("Configuration file @ '$configFilePath' couldn't be read");

        $deserializedContents = json_decode($contents);

        if (!$deserializedContents)
            throw new RuntimeException("Couldn't parse contents @ '$configFilePath'");

        $jsonMapper = new JsonMapper;
        try {
            $bakerConfig = $jsonMapper->map($deserializedContents, BakerConfiguration::class);
        } catch (\Exception $e) {
            throw new RuntimeException("Invalid configuration @ '$configFilePath': {$e->getMessage()}");
        }

        $bakerConfig->processData();
        return $bakerConfig;
    }

    public function defaultConfigFilepath(): string
    {
        $homePath = getenv('USERPROFILE') ?: getenv('HOME');
        return $homePath . '/' . self::DEFAULT_CONFIG_FILENAME;
    }
}
