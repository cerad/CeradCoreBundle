<?php

namespace Cerad\Bundle\CoreBundle\Event\Person;

use Symfony\Component\EventDispatcher\Event;

class FindByEvent extends Event
{
    protected $param;
    protected $person;
    
    public function __construct($param)
    {
        $this->param = $param;
    }
    public function getPerson()        { return $this->person;    }
    public function setPerson($person) { $this->person = $person; }

    public function getParam() { return $this->param; }   
}