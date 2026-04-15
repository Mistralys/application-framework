<?php
/**
 * @package TestDriver
 * @subpackage Testing
 */

declare(strict_types=1);

namespace TestDriver\Area\TestingScreen;

use Application\Countries\Admin\Traits\CountryRequestInterface;
use Application\Countries\Admin\Traits\CountryRequestTrait;
use Application_Admin_Area_Mode;
use TestDriver\Admin\TestingScreenInterface;
use TestDriver\Admin\TestingScreenTrait;

/**
 * Minimal test-application admin screen that consumes {@see CountryRequestTrait}.
 *
 * This class exists solely to give PHPStan a concrete consumer to analyse,
 * satisfying the project's trait consumer policy: traits must never be suppressed
 * via `trait.unused` — the test application must implement a concrete consumer
 * for every library trait.
 *
 * @package TestDriver
 * @subpackage Testing
 * @see CountryRequestInterface
 * @see CountryRequestTrait
 */
class CountryRequestScreen
    extends Application_Admin_Area_Mode
    implements TestingScreenInterface, CountryRequestInterface
{
    use TestingScreenTrait;
    use CountryRequestTrait;

    public const string URL_NAME = 'country-request';

    public static function getTestLabel() : string
    {
        return t('Country Request Trait');
    }

    protected function _renderContent() : string
    {
        $this->getCountryRequest();

        return '';
    }
}
