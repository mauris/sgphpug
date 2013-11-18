<?php

namespace SGPHPUG\Page;

use Packfire\Application\Pack\Controller as BaseController;
use SGPHPUG\Event\Event;

class Controller extends BaseController
{
    public function getEvent($eventId)
    {
        $provider = new Event();
        $provider($this->ioc);
        $this->state['event'] = $provider->get($eventId);
        $this->render();
    }
}
