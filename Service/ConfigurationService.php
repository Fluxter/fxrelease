<?php

namespace Fluxter\FXRelease\Service;

use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Model\ConfigurationVersionFile;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    private function loadConfigArray(): array
    {
        $file = ".fxrelease";
        if (!$this->fs->exists($file)) {
            throw new \Exception("The configuration file .fxrelease does not exist!");
        }

        $data = json_decode(file_get_contents($file), true);
        if ($data == null) {
            throw new \Exception("Your json could not be loaded, maybe syntax error?");
        }

        return $data;
    }

    public function getConfiguration(): Configuration
    {
        $data = $this->loadConfigArray();
        $config = new Configuration();
        
        if (!array_key_exists("apiKey", $data) && getenv("FXRELEASE_APIKEY")) {
            $config->setApiKey(getenv("FXRELEASE_APIKEY"));
        }

        $specials = ["versionFile", "versionPattern", "versionFiles"];

        $this->addVersionFiles($data, $config);
        foreach ($data as $key => $value) {
            if (in_array($key, $specials)) {
                continue;
            }

            $config->{"set" . ucfirst($key)}($value);
        }

        $this->validate($config);
        return $config;
    }

    private function addVersionFiles(array $configFile, Configuration $config): void
    {
        if (array_key_exists("versionFile", $configFile) && array_key_exists("versionPattern", $configFile)) {
            $config->addVersionFile($configFile["versionFile"], $configFile["versionPattern"]);
        }

        if (array_key_exists("versionFiles", $configFile)) {
            foreach ($configFile["versionFiles"] as $file) {
                $config->addVersionFile(new ConfigurationVersionFile($file["file"], $file["pattern"]));
            }
        }
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
