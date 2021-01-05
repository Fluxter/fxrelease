<?php

namespace Fluxter\FXRelease\Model;

class ConfigurationVersionFile
{
    private string $file;
    private string $pattern;

    public function __construct(string $file, string $pattern)
    {
        $this->file = $file;
        $this->pattern = $pattern;
    }

    /**
     * Get the value of file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the value of pattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}
