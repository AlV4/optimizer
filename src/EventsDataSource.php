<?php

namespace Optimizer;

class EventsDataSource
{
    public function getEventsSince(string $string): array
    {
        $events = [];
        $types = ['install', 'purchase', 'redirect', 'login'];
        $campaigns = (new CampaignDataSource())->getCampaigns();
        $camLength = count($campaigns);
        for($i = 0; $i < 5000; $i++){
            $type = $i % 5 === 0 ? $types[rand(1,3)] : 'install';
            $publisherId = rand(1, 10);
            $campaign = $campaigns[rand(0, $camLength - 1)];
            $events[] = new Event($type, $campaign->getId(), $publisherId);
        }
        return $events;
    }
}