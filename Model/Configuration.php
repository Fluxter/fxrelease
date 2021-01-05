<?php

namespace Fluxter\FXRelease\Model;

class Configuration
{
    private ?string $projectId = null;
    private ?string $apiKey = null;
    private string $type = "gitlab";
    private ?string $url = null;
    private array $versionFiles = [];
    private string $masterBranch = "master";
    
    /**
     * Get the value of projectId
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set the value of projectId
     *
     * @return  self
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get the value of apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set the value of apiKey
     *
     * @return  self
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get the value of type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the value of url
     *
     * @return  self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of masterBranch
     */
    public function getMasterBranch()
    {
        return $this->masterBranch;
    }

    /**
     * Set the value of masterBranch
     *
     * @return  self
     */
    public function setMasterBranch($masterBranch)
    {
        $this->masterBranch = $masterBranch;

        return $this;
    }

    /**
     * Get the value of versionFiles
     * @return ConfigurationVersionFile[]
     */
    public function getVersionFiles()
    {
        return $this->versionFiles;
    }

    public function addVersionFile(ConfigurationVersionFile $file)
    {
        $this->versionFiles[] = $file;
    }
}
