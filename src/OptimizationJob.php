<?php

namespace Optimizer;

use Optimizer\errors\CampaignDoesNotExistException;

class OptimizationJob
{
    public function run()
    {
        $campaignDS = new CampaignDataSource();
        /** @var Campaign [] $campaigns */
        $campaigns = $this->campaignsMap($campaignDS->getCampaigns());
        $campaigns[] = $campaignDS->getCampaigns()[2];
        $eventsMap = [];
        $eventsDS = new EventsDataSource();
        /** @var Event $event */
        foreach($eventsDS->getEventsSince("2 weeks ago") as $event){
// START HERE
            $this->countEventsByType($event, $eventsMap);
        }
        $this->processCampaigns($campaigns, $eventsMap);
    }

    /**
     * @param Campaign [] $campaigns
     * @param array $eventsMap
     */
    private function processCampaigns(&$campaigns, array $eventsMap)
    {
        foreach($eventsMap as $publisherId => $campaignEvents){
            foreach($campaignEvents as $campaignId => $events) {
                try{
                    $campaign = $this->getCampaignById($campaigns, $campaignId);
                }catch(CampaignDoesNotExistException $e){
                    //TODO maybe some logs here
                    print_r($e->getMessage() . "\n");
                    continue;
                }
                $this->decisionPoint($campaign, $events, $publisherId);
            }
        }
    }

    /**
     * @param Campaign $campaign
     * @param array $events
     * @param $publisherId
     */
    private function decisionPoint(Campaign &$campaign, array $events, $publisherId)
    {
        $props = $campaign->getOptimizationProps();
        if($this->isFailedCampaign($props, $events)){
            if($campaign->pushToBlacklist($publisherId)){
                $this->sendEmail("black list", $campaign, $publisherId, $events);
            }
        }elseif($campaign->removeFromBlackList($publisherId)){
            $this->sendEmail('white list', $campaign, $publisherId, $events);
        }
    }

    /**
     * @param OptimizationProps $props
     * @param array $eventCounters
     * @return bool
     */
    private function isFailedCampaign(OptimizationProps $props, array $eventCounters)
    {
        if($this->isThresholdPassed($props, $eventCounters)){
            $eventName = $props->measuredEvent;
            if(array_key_exists($eventName, $eventCounters)){
                $measuredEventsCount = $eventCounters[$eventName];
                $sourceEventsCount = $eventCounters[$props->sourceEvent];
                return floatval($measuredEventsCount) < floatval($sourceEventsCount * $props->ratioThreshold / 100 /**percent*/);
            }
            return true;
        }
        return false;
    }

    /**
     * @param OptimizationProps $props
     * @param array $eventCounters
     * @return bool
     */
    private function isThresholdPassed(OptimizationProps $props, array $eventCounters)
    {
        $eventName = $props->sourceEvent;
        return array_key_exists($eventName, $eventCounters) && $eventCounters[$eventName] > $props->threshold;
    }

    /**
     * Builds assoc structure where publisherId is first keys level,
     * campaignId - second. Ex.:
     *      [
     *        "publisherId" => [
     *            "campaignId" => [
     *                  'eventType' => 15,
     *                  'eventType1' => 6,
     *                  'eventType2' => 3,
     *                  'eventType...' => ...,
     *              ]
     *          ]
     *      ]
     * @param Event $event
     * @param array $eventsMap
     */
    private function countEventsByType(Event $event, array &$eventsMap)
    {
        $publisherId = $event->getPublisherId();
        $campaignId = $event->getCampaignId();
        $type = $event->getType();

        if(empty($eventsMap[$publisherId][$campaignId][$type])){
            $eventsMap[$publisherId][$campaignId][$type] = 0;
        }
        $eventsMap[$publisherId][$campaignId][$type]++;
    }

    /**
     * @param array $campaigns
     * @param $id
     * @return mixed
     * @throws CampaignDoesNotExistException
     */
    private function getCampaignById(array $campaigns, $id)
    {
        if(array_key_exists($id, $campaigns)){
            return $campaigns[$id];
        }
        throw new CampaignDoesNotExistException("Campaign does not exist!");
    }

    /**
     * @param array $campaigns
     * @return array
     */
    private function campaignsMap(array $campaigns)
    {
        $assoc = [];
        /**@var Campaign $campaign*/
        foreach ($campaigns as $campaign) {
            $assoc[$campaign->getId()] = $campaign;
        }
        return $assoc;
    }

    private function sendEmail(string $type, Campaign $campaign, $publisherId, array $events)
    {
        /**@var \Optimizer\OptimizationProps $p*/
        $p = $campaign->getOptimizationProps();
        print_r( "Id: {$campaign->getId()}, source: $p->sourceEvent, measured: $p->measuredEvent, threshold: $p->threshold, ratio: $p->ratioThreshold email type: '$type', publisher id: '$publisherId'\n");
        print_r($events);
        print_r("\n");
    }
}