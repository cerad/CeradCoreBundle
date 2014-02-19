<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindProjectEvent extends Event
{
    protected $search;
    protected $project;
    
    public function __construct($search)
    {
        $this->search = trim($search);
    }
    public function getProject()         { return $this->project;     }
    public function setProject($project) { $this->project = $project; }

    // Can't decide on the name
    public function getParam () { return $this->search; }   
    public function getSearch() { return $this->search; }   
}