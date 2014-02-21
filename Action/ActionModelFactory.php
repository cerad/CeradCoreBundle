<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActionModelFactory
{
    protected $dispatcher;

    public function setDispatcher(EventDispatcherInterface $dispatcher) { $this->dispatcher = $dispatcher; }
}
