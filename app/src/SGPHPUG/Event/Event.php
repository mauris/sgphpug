<?php

namespace SGPHPUG\Event;

class Event
{
    public function loadAll()
    {
        $files = glob(__DIR__ . '/*.json');
        $events = array();
        foreach ($files as $file) {
            $events[] = json_decode(file_get_contents($file), true);
        }
        return $events;
    }
}
