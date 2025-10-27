<?php

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\Groups\GenericAPIGroup;

class CountriesAPIGroup extends GenericAPIGroup
{
    private function __construct()
    {
        parent::__construct(
            'app-countries',
            (string)sb()->t('Application countries'),
            t('API methods for retrieving information on countries available in the application.')
        );
    }

    private static ?self $instance = null;

    public static function create() : self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
