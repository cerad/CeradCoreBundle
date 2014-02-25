<?php

namespace Cerad\Bundle\CoreBundle\Event\User;

use Symfony\Component\EventDispatcher\Event;

class FindUserEvent extends Event
{
    const NAME = 'CeradUserFindUserEvent';
    
    protected $search;
    protected $user;
    
    public function __construct($search)
    {
        $this->search = trim($search);
    }
    public function getUser()      { return $this->user;     }
    public function setUser($user) { $this->user = $user; }

    public function getSearch() { return $this->search; }
}