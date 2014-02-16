<?php

namespace Cerad\Bundle\CoreBundle\Event\Project;

use Symfony\Component\EventDispatcher\Event;

class FindByEvent extends Event
{
    protected $param;
    protected $project;
    
    public function __construct($param)
    {
        $this->param = $param;
    }
    public function getProject()         { return $this->project;     }
    public function setProject($project) { $this->project = $project; }

    public function getParam() { return $this->param; }   
}