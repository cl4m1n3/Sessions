<?php

namespace redcore\sessions;

use pocketmine\scheduler\Task;

class Counter extends Task{
    
    private $main;
    
    public function __construct($main){
        $this->main = $main;
    }
    public function onRun() : void{
        $this->main->onTask();
    }
}