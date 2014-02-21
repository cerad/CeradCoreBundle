<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class ActionModelFactory
{
    protected $dispatcher;

    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
    
    abstract public function create(Request $request);
}
