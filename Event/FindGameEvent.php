<?php

namespace Cerad\Bundle\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class FindGameEvent extends Event
{
    protected $project;
    protected $gameNum;
    
    protected $game;
    
    public function __construct($project,$gameNum)
    {
        $this->project = $project;
        $this->gameNum = $gameNum;
    }
    public function getGame()      { return $this->game;  }
    public function setGame($game) { $this->game = $game; }

    public function getProject() { return $this->project; }
    public function getGameNum() { return $this->gameNum; }
}