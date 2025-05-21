<?php

declare(strict_types=1);

namespace TestDriver\Admin;

trait TestingScreenTrait
{
    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return self::getTestLabel();
    }

    public function getNavigationTitle(): string
    {
        return self::getTestLabel();
    }
}
