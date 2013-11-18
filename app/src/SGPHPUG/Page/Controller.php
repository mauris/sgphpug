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
        $event = $provider->get($eventId);
        $venue = $event['fb_event']['venue'];
        $event['gmapaddress'] = urlencode($venue['city'] . ', ' . $venue['country'] . ' ' . $venue['zip']);
        $this->state['event'] = $event;
        $this->render();
    }
}
