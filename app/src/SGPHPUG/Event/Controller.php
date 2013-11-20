<?php

namespace SGPHPUG\Event;

use Packfire\Application\Pack\Controller as BaseController;

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
