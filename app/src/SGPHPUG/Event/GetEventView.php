<?php

namespace SGPHPUG\Event;

use Packfire\Application\Pack\View;

class GetEventView extends View
{
    protected function create()
    {
        $this->define('rootUrl', $this->ioc['config']->get('app', 'rootUrl'));
        $this->define($this->state);
    }
}
