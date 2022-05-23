<?php

namespace GingTeam;

use React\EventLoop\Loop;
use Workerman\Events\React\Base;

class React extends Base
{
    public function __construct()
    {
        $this->_eventLoop = Loop::get();
    }
}
