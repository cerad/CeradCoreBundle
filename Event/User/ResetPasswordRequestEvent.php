<?php

namespace Cerad\Bundle\CoreBundle\Event\User;

use Symfony\Component\EventDispatcher\Event;

class ResetPasswordRequestEvent extends Event
{
    const NAME = 'CeradUserResetPasswordRequest';
    
    protected $user;
    protected $token;
    
    public function __construct($user,$token)
    {
        $this->user  = $user;
        $this->token = $token;
    }
    public function getUser()  { return $this->user;  }
    public function getToken() { return $this->token; }
}