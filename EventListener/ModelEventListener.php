<?php

namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
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

use Cerad\Bundle\CoreBundle\Event\User\FindUserEvent;


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
        return array(
            KernelEvents::CONTROLLER => array(
                array('doRole',          -1100),
                array('doUser',          -1200),  // Logged in user
                array('doUserPerson',    -1210),  // Logged in user person
                array('doUserFind',      -1220),  // Passed as argument
                array('doProject',       -1300),
                array('doPerson',        -1400),
              //array('doProjectPerson', -1210),
                array('doModel',         -1900),
                array('doModelForm',     -1910),
            ),
            KernelEvents::VIEW => array(
                array('doView',          -2100),
            ),
        );
    }
    /* =================================================================
     * Creates and renders a view
     */
    public function doView(GetResponseForControllerResultEvent $doEvent)
    {
        if (!$doEvent->getRequest()->attributes->has('_view')) return;
        
        $request = $doEvent->getRequest();
        
        $viewServiceId = $request->attributes->get('_view');
        
        $view = $this->container->get($viewServiceId);
     
        $viewResponse = $view->renderResponse($request);
        
        $doEvent->setResponse($viewResponse);
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
        
        if (!$person) 
        {
            $message = sprintf('No Person For User, %s %s',$user->getAccountName(),$user->getPersonGuid());
            throw new NotFoundHttpException($message);
        }
        $request->attributes->set('userPerson',$person);
    }
    public function doUserFind(FilterControllerEvent $doEvent)
    {
        if (!$doEvent->getRequest()->attributes->has('_userFind')) return;
        
        $request = $doEvent->getRequest();
        
        $search = $request->attributes->get('_userFind');
        $findUserEvent = new FindUserEvent($search);
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(FindUserEvent::NAME,$findUserEvent);

        $user = $findUserEvent->getUser();
        if (!$user) throw new NotFoundHttpException('User not found ' . $search);
        
        $request->attributes->set('user',$user);
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
        
        if (!$projectSearch) 
        {
            // Use default project of non-specified
            $projectSearch = $this->container->getParameter('cerad_project_project_default');
        }
      
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
    public function doModel(FilterControllerEvent $doEvent)
    {   
        if (!$doEvent->getRequest()->attributes->has('_model')) return;
        
        $request = $doEvent->getRequest();

        $modelFactoryServiceId = $request->attributes->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
     
        $model = $modelFactory->create($request);
        
        $request->attributes->set('model',$model);
    }
    /* ==========================================================
     * The Model Form
     */
    public function doModelForm(FilterControllerEvent $doEvent)
    {   
        if (!$doEvent->getRequest()->attributes->has('_form')) return;
        
        $request = $doEvent->getRequest();
        
        $formFactoryServiceId = $request->attributes->get('_form');
        
        $formFactory = $this->container->get($formFactoryServiceId);
        
        $form = $formFactory->create($request,$request->attributes->get('model'));
        
        $request->attributes->set('form',$form);
    }
}
