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
use Symfony\Component\EventDispatcher\Event as PersonFindEvent;
use Symfony\Component\EventDispatcher\Event as PersonPlanFindEvent;

use Cerad\Bundle\PersonBundle\PersonEvents;

use Cerad\Bundle\CoreBundle\Events\ProjectEvents;
use Cerad\Bundle\CoreBundle\Event\Project\FindByEvent as ProjectFindEvent;

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
    const UserEventListenerPriority    =  -16;
    const ProjectEventListenerPriority =  -32;
    const GameEventListenerPriority    =  -64;
    const PersonEventListenerPriority  =  -64;
    const ModelRequestListenerPriority = -256;
    
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => array(
            array('doRole',          -1100),
            array('doProject',       -1200),
            array('doProjectPerson', -1210),
            array('doModel',         -1900),
            array('doModelForm',     -1910),
        ));
    }
    /* =============================================================
     * Allows protecting each route while defining the route
     * Question: Should this also take care of grabbing and injecting the user
     */
    public function doRole(FilterControllerEvent $eventx)
    {
        // Will a sub request ever change this?
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        if (!$eventx->getRequest()->attributes->has('_role')) return;
        
        $role = $eventx->getRequest()->attributes->get('_role');
        
        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted($role))
        {
            // This will be caught by the security system I think
            // TODO: Test more
            // Works for ROLE_USER - ROLE_USER
            // Test  for ROLE_USER - ROLE_ADMIN
            throw new AccessDeniedException(); 
        }
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

        // Pull the slug
        $projectSlug = $eventx->getRequest()->attributes->get('_project');
        
        // Then the project
        $projectFindEvent = new ProjectFindEvent($projectSlug);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(ProjectEvents::FindProjectBySlug,$projectFindEvent);
        
        $project = $projectFindEvent->getProject();
        
        if (!$project) throw new NotFoundHttpException('Project not found ' . $projectSlug);
        
        // Stash it
        $eventx->getRequest()->attributes->set('project',$project);
    }
    public function doProjectPerson(FilterControllerEvent $eventx)
    {
        // Will a sub request ever change projects?
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        // Only process routes asking for a project
        if (!$eventx->getRequest()->attributes->has('_projectPerson')) return;

        // Pull the person id
        $personId = $eventx->getRequest()->attributes->get('_projectPerson');
        if (!$personId)
        {
            // Suppose coud try current user person?
            throw new NotFoundHttpException('No PersonID For _projectPerson');    
        }
        
        // Find The Person
        $personFindEvent = new PersonFindEvent;
        $personFindEvent->id   = $personId;
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(PersonEvents::FindPersonById,$personFindEvent);
        
        $person = $personFindEvent->person;
        
        if (!$person) throw new NotFoundHttpException('No Person For _projectPerson ' . $personId);
        
        // Stash it
        $eventx->getRequest()->attributes->set('person',$person);
        
         // Find The Plan
        $planFindEvent = new PersonPlanFindEvent;
        $planFindEvent->project = $eventx->getRequest()->attributes->get('project');
        $planFindEvent->person  = $eventx->getRequest()->attributes->get('person');
        
      //$dispatcher = $this->container->get('event_dispatcher');
        
        $dispatcher->dispatch(PersonEvents::FindPlanByProjectAndPerson,$planFindEvent);
        
        $plan = $planFindEvent->plan;
        
        if (!$plan) throw new NotFoundHttpException('No Plan For _projectPerson ' . $personId);
        
        // Stash it
        $eventx->getRequest()->attributes->set('plan',$plan);

    }
    /* ==========================================================
     * The Model
     */
    public function doModel(FilterControllerEvent $eventx)
    {
        if (HttpKernel::MASTER_REQUEST != $eventx->getRequestType()) return;
        
        $request = $eventx->getRequest();
        
        if (!$request->attributes->has('_model')) return;

        $modelFactoryServiceId = $request->attributes->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
        
        $model = $modelFactory->create($request);
        
        $request->attributes->set('model',$model);
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
