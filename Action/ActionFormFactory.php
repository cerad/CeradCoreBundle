<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\FormFactoryInterface;

abstract class ActionFormFactory
{
    protected $router;
    protected $formFactory;
    
    public function setRouter(RouterInterface $router)     
    { 
        $this->router = $router;
    }
    public function setFormFactory(FormFactoryInterface $formFactory) 
    { 
        $this->formFactory = $formFactory; 
    }
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
    abstract public function create(Request $request, $model);
}
