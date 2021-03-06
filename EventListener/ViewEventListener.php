<?php

namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
class ModelEventListener extends ContainerAware implements EventSubscriberInterface
{
    
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => array(
            array('doRole',          -1100),
            array('doUser',          -1200),
            array('doUserPerson',    -1210),
            array('doProject',       -1300),
            array('doPerson',        -1400),
          //array('doProjectPerson', -1210),
            array('doModel',         -1900),
            array('doModelForm',     -1910),
        ));
    }
    /* =============================================================
     * Allows protecting each route while defining the route
     * Question: Should this also take care of grabbing and injecting the user
     */
    public function doRole(FilterControllerEvent $doEvent)
    {
        if (!$doEvent->getRequest()->attributes->has('_role')) return;
        
        $role = $doEvent->getRequest()->attributes->get('_role');
        
        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted($role))
        {
            throw new AccessDeniedException(); 
        }
    }
    public function doUser(FilterControllerEvent $doEvent)
    {
        if (!$doEvent->getRequest()->attributes->has('_user')) return;
        if ( $doEvent->getRequest()->attributes->has( 'user')) return; // Already got
//die('doUser 1');
        $securityContext = $this->container->get('security.context');
        
        // Follow the logic in S2 Controller::getUser
        $doEvent->getRequest()->attributes->set('user',null);
        
        $token = $securityContext->getToken();
        if (!$token) return;
        
        $user = $token->getUser(); // die('doUser Token');
        if (!is_object($user)) return;
      //die('doUser 2');
      //die($user->getAccountName());
        $doEvent->getRequest()->attributes->set('user',$user);
    }
    public function doUserPerson(FilterControllerEvent $doEvent)
    {
        if (!$doEvent->getRequest()->attributes->has('_userPerson')) return;
      //die('doUserPerson 1');
        // Need a user first
        $request = $doEvent->getRequest();
        if (!$request->attributes->has('user'))
        {
           //die('doUserPerson 2');
            $request->attributes->set('_user',true);
          //die('doUserPerson 3');
            $this->doUser($doEvent);
          //die('doUserPerson 4');
            if (!$request->attributes->has('user')) return;
        }
      //die('doUserPerson 5');
        $user = $request->attributes->get('user');
        
        if (!$user) return;
        
        // Find The Person
        $findPersonEvent = new FindPersonEvent($user->getPersonGuid());
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(PersonEvents::FindPerson,$findPersonEvent);
        
        $person = $findPersonEvent->getPerson();
        
        if (!$person) throw new NotFoundHttpException('No Person For User');

        $request->attributes->set('userPerson',$person);
    }
    /* =======================================================
     * I think we want this to be completely self contained
     */
    public function doProject(FilterControllerEvent $eventx)
    {
        // Will a sub request ever change projects?
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        // Only process routes asking for a project
        if (!$eventx->getRequest()->attributes->has('_project')) return;

        // Pull the search
        $projectSearch = $eventx->getRequest()->attributes->get('_project');
        
        // Then the project
        $findProjectEvent = new FindProjectEvent($projectSearch);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(ProjectEvents::FindProject,$findProjectEvent);
        
        $project = $findProjectEvent->getProject();
        
        if (!$project) throw new NotFoundHttpException('Project not found ' . $projectSearch);
        
        // Stash it
        $eventx->getRequest()->attributes->set('project',$project);
    }
    public function doPerson(FilterControllerEvent $eventx)
    {
        // Will a sub request ever change projects?
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        // Only process routes asking for a project
        $request = $eventx->getRequest();
        if (!$request->attributes->has('_person')) return;

        // Pull the person id
        $personId = $request->attributes->get('_person');
        
        // Find The Person
        $findPersonEvent = new FindPersonEvent($personId);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(PersonEvents::FindPerson,$findPersonEvent);
        
        $person = $findPersonEvent->getPerson();
        
        if (!$person) throw new NotFoundHttpException('No Person For %d ' . $personId);
        
        // Stash it
        $request->attributes->set('person',$person);
    }
    /* ==========================================================
     * The Model
     * Does get called in sub requests
     */
    public function doModel(FilterControllerEvent $eventx)
    {   
      //if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        $request = $eventx->getRequest();
        
        if (!$request->attributes->has('_model')) return;

        $modelFactoryServiceId = $request->attributes->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
     
        $model = $modelFactory->create($request);
        
        $request->attributes->set('model',$model);
        
        return;
        
      //$sub = (HttpKernel::MASTER_REQUEST == $eventx->getRequestType()) ? false : true;
        
        if ($sub)
        {
            //$request->attributes->set('foo','bar2');
            //return;
        }
       
        if (HttpKernel::MASTER_REQUEST == $eventx->getRequestType())
        {
            $request->attributes->set('model',$model);
            return;
        }
        $request->attributes->set('foo',$model);
    }
    /* ==========================================================
     * The Model Form
     */
    public function doModelForm(FilterControllerEvent $eventx)
    {
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        $request = $eventx->getRequest();
        
        if (!$request->attributes->has('_form')) return;

        $formFactoryServiceId = $request->attributes->get('_form');
        
        $formFactory = $this->container->get($formFactoryServiceId);
        
        $form = $formFactory->create($request,$request->attributes->get('model'));
        
        $request->attributes->set('form',$form);
    }
    /* =====================================================================
     * Old stuff
     */
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
    {die('CoreRequestListener');
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
