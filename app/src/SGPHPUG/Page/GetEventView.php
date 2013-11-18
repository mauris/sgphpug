<?php

namespace SGPHPUG\Page;

use Packfire\Application\Pack\View;

class GetEventView extends View
{
    protected function create()
    {
        $this->define($this->state);
    }
}
