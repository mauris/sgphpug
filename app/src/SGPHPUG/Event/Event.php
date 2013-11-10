<?php

namespace SGPHPUG\Event;

use Packfire\FuelBlade\ConsumerInterface;
use Packfire\DateTime\TimeSpan;

class Event implements ConsumerInterface
{
    protected $cache;

    public function loadAll()
    {
        $cache = $this->cache;
        $token = null;
        if ($cache->check('fb_access_token')) {
            $token = $cache->get('fb_access_token');
        }
        if (!$token) {
            $details = array(
                'appId'  => '1374863032759788',
                'secret' => '3834111b049efc7d7290d18fa184b053',
            );
            $token = str_replace('access_token=', '', file_get_contents(sprintf('https://graph.facebook.com/oauth/access_token?client_id=%s&client_secret=%s&grant_type=client_credentials', $details['appId'], $details['secret'])));
            $cache->set('fb_access_token', $token, new TimeSpan(216000));
        }

        $files = glob(__DIR__ . '/*.json');
        $events = array();
        foreach ($files as $file) {
            $events[] = json_decode(file_get_contents($file), true);
        }
        return $events;
    }

    public function __invoke($container)
    {
        $this->cache = $container['cache'];
    }
}
