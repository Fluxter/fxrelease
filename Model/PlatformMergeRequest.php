<?php

namespace Fluxter\FXRelease\Model;

class PlatformMergeRequest
{
    private int $id;
    private string $url;

    public function __construct(int $id, string $url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of url
     */
    public function getUrl()
    {
        return $this->url;
    }
}
