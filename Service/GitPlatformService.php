<?php

namespace Fluxter\FXRelease\Service;

use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Service\GitPlatform\GitlabPlatformService;
use Fluxter\FXRelease\Service\GitPlatform\ReleasePlatformProviderInterface;

class GitPlatformService
{
    private static $platforms = [
        "gitlab" => GitlabPlatformService::class
    ];

    public function getAllPlatforms(): array
    {
        return self::$platforms;
    }

    public function getPlatform(Configuration $config): ReleasePlatformProviderInterface
    {
        /** @var ReleasePlatformProviderInterface */
        $platform = new self::$platforms[$config->getType()]($config);
        return $platform;
    }
}
