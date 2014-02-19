<?php
namespace Cerad\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Cerad\Bundle\CoreBundle\Events\ProjectEvents;

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
        
        // By slug
        $event1 = new FindProjectEvent('classic2014');
        $dispatcher->dispatch(ProjectEvents::FindProject,$event1);
        $project1 = $event1->getProject();
        echo sprintf("Project A %d %s %s '%s'\n",$project1->getId(),$project1->getSlug(),$project1->getKey(),$project1->getName());
        
        // By key
        $event2 = new FindProjectEvent($project1->getKey());
        $dispatcher->dispatch(ProjectEvents::FindProject,$event2);
        $project2 = $event2->getProject();
        echo sprintf("Project B %d %s %s '%s'\n",$project2->getId(),$project2->getSlug(),$project2->getKey(),$project2->getName());
        
        return;
        
        /* ==========================================
         * Just because
         */
        if ($input || $output);
    }
 }
?>
