<?php
namespace Cerad\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;
use Cerad\Bundle\CoreBundle\Events\ProjectEvents;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;
use Cerad\Bundle\CoreBundle\Event\FindProjectEvent;

class TestEventsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName       ('cerad_core__events_test');
        $this->setDescription('Test Core Events');
    }
    protected function getService  ($id)   { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->getService('event_dispatcher');
        
        // Project By slug
        $projectEvent1 = new FindProjectEvent('classic2014');
        $dispatcher->dispatch(ProjectEvents::FindProject,$projectEvent1);
        $project1 = $projectEvent1->getProject();
        echo sprintf("Project A %d %s %s '%s'\n",$project1->getId(),$project1->getSlug(),$project1->getKey(),$project1->getName());
        
        // Project By key
        $projectEvent2 = new FindProjectEvent($project1->getKey());
        $dispatcher->dispatch(ProjectEvents::FindProject,$projectEvent2);
        $project2 = $projectEvent2->getProject();
        echo sprintf("Project B %d %s %s '%s'\n",$project2->getId(),$project2->getSlug(),$project2->getKey(),$project2->getName());
        
        echo sprintf("\n");
        
        // Person by id
        $personEvent1 = new FindPersonEvent(1);
        $dispatcher->dispatch(PersonEvents::FindPerson,$personEvent1);
        $person1 = $personEvent1->getPerson();
        echo sprintf("Person  A %d %s '%s'\n",$person1->getId(),$person1->getGuid(),$person1->getName()->full);
        
        // Person by guid
        $personEvent2 = new FindPersonEvent($person1->getGuid());
        $dispatcher->dispatch(PersonEvents::FindPerson,$personEvent2);
        $person2 = $personEvent2->getPerson();
        echo sprintf("Person  B %d %s '%s'\n",$person2->getId(),$person2->getGuid(),$person2->getName()->full);
        
        // Person by fed key
        $personFed = null;
        if (!$personFed) $personFed = $person1->getFed('USSFC',false);
        if (!$personFed) $personFed = $person1->getFed('AYSOV',false);
        $personFedKey = $personFed->getFedKey();
        
        $personEvent3 = new FindPersonEvent($personFedKey);
        $dispatcher->dispatch(PersonEvents::FindPerson,$personEvent3);
        $person3 = $personEvent3->getPerson();
        echo sprintf("Person  C %d %s '%s' %s\n",$person3->getId(),$person3->getGuid(),$person3->getName()->full,$personFedKey);
        
        $personEvent4 = new FindPersonEvent(substr($personFedKey,5));
        $dispatcher->dispatch(PersonEvents::FindPerson,$personEvent4);
        $person4 = $personEvent4->getPerson();
        echo sprintf("Person  D %d %s '%s' %s\n",$person4->getId(),$person4->getGuid(),$person4->getName()->full,$personFedKey);
        
        return;
        
        /* ==========================================
         * Just because
         */
        if ($input || $output);
    }
 }
?>
