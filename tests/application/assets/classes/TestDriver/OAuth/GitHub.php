<?php

declare(strict_types=1);

class TestDriver_OAuth_GitHub extends Application_OAuth_Strategy_GitHub
{
    public function getClientID(): string
    {
        return 'github_client_id';
    }

    public function getClientSecret(): string
    {
        return 'github_client_secret';
    }
}
