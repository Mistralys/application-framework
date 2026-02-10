<?php

declare(strict_types=1);

use Connectors\Connector\BaseConnector;

class Connectors_Connector_LegacyNamingConnector extends BaseConnector
{
    protected function checkRequirements(): void
    {
    }

    public function getURL(): string
    {
        return '';
    }
}
