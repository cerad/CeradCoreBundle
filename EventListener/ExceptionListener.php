<?php

namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/* =============================================================
 * This causes core to depend on other bundles
 * Really want each bundle to depend on the core
 */
use Cerad\Bundle\CoreBundle\Events\PersonEvents;
use Cerad\Bundle\CoreBundle\Events\ProjectEvents;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectEvent;

//  Cerad\Bundle\CoreBundle\Event\Person\FindPlanByProjectAndPersonEvent;

/* ========================================================
 * Rather poorly named but takes care of creating the model,form and possible view
 * 
 * It will probably implement the role listener as well
 * 
 * App Request Priority
 * 
 * -256 Role/Model/Form/View
 */
class ExceptionListener extends ContainerAware implements EventSubscriberInterface
{
    
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::EXCEPTION => array(
            array('doException', 200),
        ));
    }
    /* =============================================================
     * Allows protecting each route while defining the route
     * Question: Should this also take care of grabbing and injecting the user
     */
    public function doException(GetResponseForExceptionEvent $doEvent)
    {
        return;
        
        $exception = $doEvent->getException();
        
        die('Exception caught ' . $exception->getMessage());
        
        if (!$doEvent->getRequest()->attributes->has('_role')) return;
        
        $role = $doEvent->getRequest()->attributes->get('_role');
        
        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted($role))
        {
            throw new AccessDeniedException(); 
        }
    }
}
