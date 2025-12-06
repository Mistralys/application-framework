<?php

declare(strict_types=1);

namespace Application\Locales\API;

use Application\API\Groups\APIGroupInterface;

class LocalesAPIGroup implements APIGroupInterface
{
    public const string GROUP_NAME = 'LocalesAPI';

    private static ?LocalesAPIGroup $instance = null;

    public static function getInstance(): LocalesAPIGroup
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getID(): string
    {
        return self::GROUP_NAME;
    }

    public function getLabel(): string
    {
        return 'Locales';
    }

    public function getDescription(): string
    {
        return 'APIs related to the application locales management.';
    }
}
