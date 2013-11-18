<?php

namespace SGPHPUG\Event;

use Packfire\FuelBlade\ConsumerInterface;
use Packfire\DateTime\TimeSpan;
use Packfire\DateTime\DateTime;
use Packfire\Octurlpus\Octurlpus;

class Event implements ConsumerInterface
{
    protected $cache;

    protected $octurlpus;

    protected $token;

    public function get($eventId)
    {
        $event = array();
        if (file_exists(__DIR__ . '/' . $eventId . '.json')) {
            $event = $this->loadFile(__DIR__ . '/' . $eventId . '.json');
        }
        return $event;
    }

    public function loadAll()
    {
        $files = array_reverse(glob(__DIR__ . '/*.json'));
        $events = array();
        foreach ($files as $file) {

            $events[] = $this->loadFile($file);
        }
        return $events;
    }

    protected function loadFile($file)
    {
        $event = json_decode(file_get_contents($file), true);
        $event['eventId'] = basename($file, '.json');
        if ($event['fb_event']) {
            $event['fb_event'] = $this->loadFacebookEvent($event['fb_event'], $this->token);
            $event['date'] = $event['fb_event']['datetime']->format('d M Y, D');
        }

        $resourcesUrl = array();
        foreach ($event['presentations'] as &$presentation) {
            if (isset($presentation['resources'])) {
                $resources = array();
                foreach ($presentation['resources'] as $url) {
                    $resources[] = $this->loadResource($url);
                }
                $presentation['resources'] = $resources;
            }
        }
        return $event;
    }

    protected function loadResource($url)
    {
        $cache = $this->cache;
        $urlhash = hash('sha1', $url);

        $resource = null;
        if ($cache->check('octurlpus-' . $urlhash)) {
            $resource = $cache->get('octurlpus-' . $urlhash);
        }
        if (!$resource) {
            $resource = $this->octurlpus->request($url);
            $resource['thumbnail'] = isset($resource['thumbnail']) ? $resource['thumbnail'] : $resource['thumbnail_url'];
            $cache->set('octurlpus-' . $urlhash, $resource, new TimeSpan(216000));
        }
        return $resource;
    }

    protected function loadFacebookEvent($eventId)
    {
        $cache = $this->cache;
        $fbevent = null;
        if ($cache->check('fbevent-' . $eventId)) {
            $fbevent = $cache->get('fbevent-' . $eventId);
        }
        if (!$fbevent) {
            $fb_url = 'https://graph.facebook.com/' . $eventId .'?access_token=' . $this->token;
            $fbevent = json_decode(file_get_contents($fb_url), true);
            $cache->set('fbevent-' . $eventId, $fbevent, new TimeSpan(216000));
        }
        $start = DateTime::fromString($fbevent['start_time']);
        $start->timezone(8);
        $end = DateTime::fromString($fbevent['end_time']);
        $end->timezone(8);

        $fbevent['datetime'] = $start;
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
        $this->octurlpus = new Octurlpus();
        $this->cache = $container['cache'];
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
        $this->token = $token;
    }
}
