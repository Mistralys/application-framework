<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Admin;

use Application\Admin\Area\BaseMode;
use Application\Interfaces\Admin\RequestTypes\RequestCountryInterface;
use Application\Traits\Admin\RequestTypes\RequestCountryTrait;

/***
 * Stub used to enable static analysis of the trait {@see RequestCountryTrait}.
 */
final class CountryScreenStub extends BaseMode implements RequestCountryInterface
{
    use RequestCountryTrait;

    public function getURLName(): string
    {
        return 'country-screen-stub';
    }

    public function getNavigationTitle(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getRequiredRight(): string
    {
        return '';
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }
}
