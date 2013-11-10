<?php

namespace SGPHPUG\Event;

use Packfire\FuelBlade\ConsumerInterface;
use Packfire\DateTime\TimeSpan;
use Packfire\DateTime\DateTime;

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

        $files = array_reverse(glob(__DIR__ . '/*.json'));
        $events = array();
        foreach ($files as $file) {
            $event = json_decode(file_get_contents($file), true);
            if ($event['fb_event']) {
                $event['fb_event'] = $this->loadFacebookEvent($event['fb_event'], $token);
            }

            $resources = array();
            foreach ($event['presentations'] as $presentation) {
                $resources = array_merge($resources, $presentation['resources']);
            }

            $event['resources'] = $resources;

            $events[] = $event;
        }
        return $events;
    }

    protected function loadFacebookEvent($eventId, $token)
    {
        $cache = $this->cache;
        $fbevent = null;
        if ($cache->check('fbevent-' . $eventId)) {
            $fbevent = $cache->get('fbevent-' . $eventId);
        }
        if (!$fbevent) {
            $fb_url = 'https://graph.facebook.com/' . $eventId .'?access_token=' . $token;
            $fbevent = json_decode(file_get_contents($fb_url), true);
            $cache->set('fbevent-' . $eventId, $fbevent, new TimeSpan(216000));
        }
        $start = DateTime::fromString($fbevent['start_time']);
        $start->timezone(8);
        $end = DateTime::fromString($fbevent['end_time']);
        $end->timezone(8);

        $fbevent['start_time'] = $start->format('d M Y, l, g:i a');
        $isSameDate = self::isSameDate($start->toTimestamp(), $end->toTimestamp());
        $fbevent['end_time'] = $end->format($isSameDate ? 'g:i a' : 'd M Y, l, g:i a');

        return $fbevent;
    }

    protected static function isSameDate($date1, $date2)
    {
        return gmdate('dMY', $date1) == gmdate('dMY', $date2);
    }

    public function __invoke($container)
    {
        $this->cache = $container['cache'];
    }
}
