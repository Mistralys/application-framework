<?php

declare(strict_types=1);

class TestDriver_OAuth_Google extends Application_OAuth_Strategy_Google
{
    public function getClientID(): string
    {
        return 'google_client_id';
    }

    public function getClientSecret(): string
    {
        return 'google_client_secret';
    }
}
