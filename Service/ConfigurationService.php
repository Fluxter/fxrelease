<?php

namespace Fluxter\FXRelease\Service;

use Fluxter\FXRelease\Model\Configuration;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationService
{
    private Filesystem $fs;
    private GitPlatformService $gitPlatformService;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->gitPlatformService = new GitPlatformService();
    }

    public function getConfiguration(): Configuration
    {
        $file = ".fxrelease";

        if (!$this->fs->exists($file)) {
            throw new \Exception("The configuration file .fxrelease does not exist!");
        }

        $data = json_decode(file_get_contents($file), true);
        $config = new Configuration();
        
        if (!array_key_exists("apiKey", $data) && getenv("FXRELEASE_APIKEY")) {
            $config->setApiKey(getenv("FXRELEASE_APIKEY"));
        }
        foreach ($data as $key => $value) {
            $config->{"set" . ucfirst($key)}($value);
        }

        $this->validate($config);
        return $config;
    }
    
    private function validate(Configuration $config): bool
    {
        if (!array_key_exists($config->getType(), $this->gitPlatformService->getAllPlatforms())) {
            throw new \Exception("Invalid Type {$config->getType()}! Available: " . join(",", array_keys($this->gitPlatformService->getAllPlatforms())));
        }

        if ($config->getApiKey() == null || strlen(trim($config->getApiKey())) == 0) {
            throw new \Exception("no api key found! Please specify FXRELEASE_APIKEY in your environment or apiKey in the .fxrelease config file!");
        }
        return true;
    }
}
