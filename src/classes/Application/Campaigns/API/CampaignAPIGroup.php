<?php

declare(strict_types=1);

namespace Application\Campaigns\API;

use Application\API\Groups\APIGroupInterface;

class CampaignAPIGroup implements APIGroupInterface
{
    public const string GROUP_ID = 'Campaigns';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getID(): string
    {
        return self::GROUP_ID;
    }

    public function getLabel(): string
    {
        return 'Campaigns';
    }

    public function getDescription(): string
    {
        return 'APIs for managing campaigns.';
    }
}
