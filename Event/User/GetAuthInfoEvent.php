<?php

namespace Cerad\Bundle\CoreBundle\Event\User;

use Symfony\Component\EventDispatcher\Event;

class GetAuthInfoEvent extends Event
{
    const NAME = 'CeradUserGetAuthInfo';
    
    public $request;
    public $username;
    public $error;
    
    public function __construct($request)
    {
        $this->request = $request;
    }
}