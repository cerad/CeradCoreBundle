<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\FormFactoryInterface;

class ActionFormFactory
{
    protected $router;
    protected $dispatcher;
    protected $formFactory;
    
    public function setRouter(RouterInterface $router)     
    { 
        $this->router = $router;
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher) 
    { 
        $this->dispatcher = $dispatcher; 
    }
    public function setFormFactory(FormFactoryInterface $formFactory) 
    { 
        $this->formFactory = $formFactory; 
    }
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
}
