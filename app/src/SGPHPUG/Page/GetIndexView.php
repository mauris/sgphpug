<?php

namespace SGPHPUG\Page;

use Packfire\Application\Pack\View;
use SGPHPUG\Event\Event;

class GetIndexView extends View
{
    protected function create()
    {
        $this->define('rootUrl', $this->ioc['config']->get('app', 'rootUrl'));
        $provider = new Event();
        $provider($this->ioc);
        $this->define('events', $provider->loadAll());
    }
}
