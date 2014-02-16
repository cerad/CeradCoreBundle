<?php

namespace Cerad\Bundle\CoreBundle\Event\Person;

use Symfony\Component\EventDispatcher\Event;

class FindPlanByProjectAndPersonEvent extends Event
{
    protected $project;
    protected $person;
    protected $plan;
    
    // Accepts either object or key/guid/name
    public function __construct($project,$person)
    {
        $this->project = $project; // object or projectKey
        $this->person  = $person;  // object or personGuid or personName
    }
    public function getPlan()      { return $this->plan;  }
    public function setPlan($plan) { $this->plan = $plan; }

    public function getProject() { return $this->project; }   
    public function getPerson () { return $this->person;  }
}