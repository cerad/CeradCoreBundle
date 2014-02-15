<?php
namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// This is a little bit iffy but want seemless what to get user person
use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Cerad\Bundle\PersonBundle\PersonEvents;

/* ========================================================
 * Rather poorly named but takes care of creating the model,form and possible view
 * 
 * It will probably implement the role listener as well
 * 
 * App Request Priority
 * 
 * -256 Role/Model/Form/View
 */
class CoreRequestListener extends ContainerAware implements EventSubscriberInterface
{
    const UserEventListenerPriority    =  -16;
    const ProjectEventListenerPriority =  -32;
    const GameEventListenerPriority    =  -64;
    const PersonEventListenerPriority  =  -64;
    const ModelRequestListenerPriority = -256;
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(array('onKernelRequest', self::ModelRequestListenerPriority),
        ));
    }
    /* =============================================================
     * Allows protecting each route while defining the route
     * Question: Should this also take care of grabbing and injecting the user
     */
    protected function onKernelRequestRole(GetResponseEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_role')) return;
        
        $role = $event->getRequest()->attributes->get('_role');
        if (!$role) return;
        
        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted($role))
        {
            // This will be caught by the security system I think
            // TODO: Test more
            throw new AccessDeniedException(); 
        }
    }
    protected function onKernelRequestUserPerson(GetResponseEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_user_person')) return;
        
        $securityContext = $this->container->get('security.context');
        
        // First the user
        $token = $securityContext->getToken();
        if (!$token) throw new AccessDeniedException();

        $user = $token->getUser();
        if (!is_object($user)) throw new AccessDeniedException();
        
        $request = $event->getRequest();
        $request->attributes->set('user',$user);
 
        // Then the person
        $event = new PersonFindEvent;
        $event->guid   = $user->getPersonGuid();
        $event->person = null;
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(PersonEvents::FindPersonByGuid,$event);
        
        $userPerson = $event->person;
        
        if (!$userPerson) throw new AccessDeniedException();
        
        // Cross link
        $userPerson->setUser($user);
        $user->setPerson($userPerson);
        
        $request->attributes->set('userPerson',$userPerson);
    }
    /* ======================================================
     * This might be going too far
     * But it wolud be nice in some cases
     */
    protected function onKernelRequestUserPersonPlan(GetResponseEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_user_person_plan')) return;
    }
    /* =======================================================
     * Main processor
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Will a sub request ever change this?
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) return;
        
        // Process any roles stuff
        $this->onKernelRequestRole($event);
        
        // Grab the user person is asked
        $this->onKernelRequestUserPerson($event);
        
        // Only process routes asking for a model
        if (!$event->getRequest()->attributes->has('_model')) return;
        
        // Only process routes with a model
        $request      = $event->getRequest();
        $requestAttrs = $request->attributes;
        
        $modelFactoryServiceId = $requestAttrs->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
        
        $model = $modelFactory->create($request);
        
        $requestAttrs->set('model',$model);
        
        // Have a form?
        $formFactoryServiceId = $requestAttrs->get('_form');
        if (!$formFactoryServiceId) return;
        
        $formFactory = $this->container->get($formFactoryServiceId);
       
        $form = $formFactory->create($request,$model);
        
        $requestAttrs->set('form',$form);
    }
}
