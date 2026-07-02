<?php
namespace App\Services;

use App\Exceptions\Config\ConfigFileNotFoundException;
use App\Exceptions\Config\InvalidConfigContentsException;
use App\Exceptions\Config\MalformedConfigContentsException;
use App\Models\Config\BakerConfiguration;
use JsonMapper;
use RuntimeException;

class ConfigService
{
    public const DEFAULT_CONFIG_FILENAME = '.config.baker.json';

    /**
     * Reads and parses the configuration file specified.
     *
     * @return BakerConfiguration The configuration stored in a JSON format in the
     * specified file.
     * @throws MalformedConfigContentsException If the JSON of the configuration file is malformed.
     * @throws ConfigFileNotFoundException Config file not found.
     * @throws InvalidConfigContentsException The configuration in the file doesn't follow the configuration data schema.
     */
    public function readConfiguration(?string $configFilePath = null): BakerConfiguration
    {
        $configFilePath = $configFilePath ?? $this->defaultConfigFilepath();

        $contents = file_get_contents($configFilePath);

        if (!$contents)
            throw new ConfigFileNotFoundException($configFilePath);

        $deserializedContents = json_decode($contents);

        if (!$deserializedContents)
            throw new MalformedConfigContentsException($configFilePath);

        $jsonMapper = new JsonMapper;
        try {
            $bakerConfig = $jsonMapper->map($deserializedContents, BakerConfiguration::class);
        } catch (\Exception $e) {
            throw new InvalidConfigContentsException($configFilePath, $e);
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
