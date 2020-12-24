<?php

namespace Fluxter\FXRelease\Model;

class PlatformMilestone
{
    private int $id;
    private int $globalId;
    private string $name;
    
    public function __construct(int $id, int $globalId, string $name)
    {
        $this->id = $id;
        $this->globalId = $globalId;
        $this->name =$name;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get the value of globalId
     */ 
    public function getGlobalId()
    {
        return $this->globalId;
    }
}
