<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Screens\Mode\Create;

use Application\Countries\CountryException;

class CreateWizardException extends CountryException
{
    public const int ERROR_NO_COUNTRY_SELECTED = 177501;
}
