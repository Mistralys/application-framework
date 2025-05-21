<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Create;

use Application\Countries\CountryException;

class CreateWizardException extends CountryException
{
    public const ERROR_NO_COUNTRY_SELECTED = 177501;
}
