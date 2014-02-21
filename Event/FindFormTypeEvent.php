<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindFormTypeEvent extends Event
{
    protected $search;
    protected $formType;
    
    public function __construct($search)
    {
        $this->search = trim($search);
    }
    public function getFormType()          { return $this->formType;      }
    public function setFormType($formType) { $this->formType = $formType; }

    public function getSearch() { return $this->search; }
}