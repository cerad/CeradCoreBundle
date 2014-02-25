<?php

namespace Cerad\Bundle\CoreBundle\Event\User;

use Symfony\Component\EventDispatcher\Event;

class LoginUserEvent extends Event
{
    const NAME = 'CeradUserLoginUser';
    
    public $request;
    public $user;
    
    public function __construct($request,$user)
    {
        $this->user    = $user;
        $this->request = $request;
    }
}