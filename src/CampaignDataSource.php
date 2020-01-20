<?php

namespace Optimizer;

class CampaignDataSource
{
    /**
     * @return Campaign []
     */
    public function getCampaigns(): array
    {

        /** @var Campaign [] $campaigns */
        $campaigns = [
            new Campaign(1,'install', 'purchase', 10, 5.5),
            new Campaign(2, 'install', 'redirect', 20),
            new Campaign(3, 'install', 'login', 30, 90),
        ];
        return $campaigns;
    }
}