<?php

namespace Optimizer;

class Campaign
{
    /** @var OptimizationProps $optProps */
    private $optProps;

    /** @var int */
    private $id;

    /** @var array */
    private $publisherBlacklist = [1,2,3,9];

    /** @var array */
    private $publisherBlacklistQueue = [];

    const DEFAULT_THRESHOLD = 100;

    const DEFAULT_RATIO_THRESHOLD = 10;

    /**
     * Campaign constructor.
     * @param int $id
     * @param string $source
     * @param string $measured
     * @param int $threshold
     * @param float $ratioThreshold
     */
    public function __construct(int $id, string $source, string $measured, int $threshold = self::DEFAULT_THRESHOLD, float $ratioThreshold = self::DEFAULT_RATIO_THRESHOLD)
    {
        $this->id = $id;
        $optProps = new OptimizationProps();
        $optProps->sourceEvent = $source;
        $optProps->measuredEvent = $measured;
        $optProps->threshold = $threshold;
        $optProps->ratioThreshold = $ratioThreshold;
        $this->optProps = $optProps;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getOptimizationProps()
    {
        return $this->optProps;
    }

    /**
     * @param $publisherId
     * @return bool
     */
    public function pushToBlacklist($publisherId)
    {
        if(!in_array($publisherId, $this->publisherBlacklist)){
            $this->publisherBlacklistQueue[] = $publisherId;
            return true;
        }
        return false;
    }

    /**
     * @param $publisherId
     * @return bool
     */
    public function removeFromBlackList($publisherId)
    {
        foreach ($this->publisherBlacklist as $idx => $value) {
            if($publisherId === $value){
                unset($this->publisherBlacklist[$idx]);
                return true;
            }
        }
        return false;
    }

    public function getBlackList()
    {
        return $this->publisherBlacklist;
    }

    public function saveBlacklist($blacklist)
    {
// dont implement
    }
}