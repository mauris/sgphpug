<?php

namespace SGPHPUG\Page;

use Packfire\Application\Pack\View;
use SGPHPUG\Event\Event;

class GetIndexView extends View
{
    protected function create()
    {
        $provider = new Event();
        $this->define('events', $provider->loadAll());
    }
}
