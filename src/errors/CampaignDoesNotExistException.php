<?php


namespace Optimizer\errors;


use Throwable;

class CampaignDoesNotExistException extends \Exception
{
    /**
     * CampaignDoesNotExistException constructor.
     */
    public function __construct($msg)
    {
        $this->message = $msg;
        parent::__construct();
    }

}