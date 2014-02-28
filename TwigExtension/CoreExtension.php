<?php

namespace Cerad\Bundle\CoreBundle\TwigExtension;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Symfony\Component\HttpKernel\Controller\ControllerReference;

class CoreExtension extends \Twig_Extension
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('route', array($this, 'route')),
            new \Twig_SimpleFunction('local', array($this, 'local')),
        );
    }
    public function route($routeName, $attributes = array(), $query = array())
    {
        $route = $this->router->getRouteCollection()->get($routeName);
        if (!$route)
        {
            throw new RouteNotFoundException(sprintf("Twig route not found: %s",$routeName));
        }
        $defaults = array_merge($route->getDefaults(),$attributes);
        
      //$defaults['_route'] = $routeName;
        
        $controller = $defaults['_controller'];
        
        return new ControllerReference($controller, $defaults, $query);
    }
    public function local($self,$name)
    {
        $dir = dirname($self->getTemplateName());
        return $dir . DIRECTORY_SEPARATOR . $name;
    }
    public function getName() { return 'cerad_core'; }
}