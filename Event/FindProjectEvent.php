<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Cerad\Bundle\CoreBundle\Event\Project\FindByEvent;

class FindProjectEvent extends FindByEvent
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