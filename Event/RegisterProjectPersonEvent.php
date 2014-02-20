<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class RegisterProjectPersonEvent extends Event
{
    protected $project;
    protected $person;
    protected $personFed;
    protected $personPlan;
    
    public function __construct($project,$person,$personPlan,$personFed)
    {
        $this->project    = $project;
        $this->person     = $person;
        $this->personFed  = $personFed;
        $this->personPlan = $personPlan;
    }
    public function getProject()    { return $this->project;    }
    public function getPerson()     { return $this->person;     }
    public function getPersonFed()  { return $this->personFed;  }
    public function getPersonPlan() { return $this->personPlan; }
}