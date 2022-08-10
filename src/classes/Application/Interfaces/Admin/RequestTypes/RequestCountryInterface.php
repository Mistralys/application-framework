<?php
/**
 * @package Application
 * @subpackage Traits
 * @see \Application\Interfaces\Admin\RequestTypes\RequestCountryInterface
 */

declare(strict_types=1);

namespace Application\Interfaces\Admin\RequestTypes;

use Application\Traits\Admin\RequestTypes\RequestCountryTrait;
use Application_Admin_ScreenInterface;
use Application_Countries;
use Application_Countries_Country;

/**
 * Interface for the {@see RequestCountryTrait} trait.
 *
 * @package Application
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RequestCountryTrait
 */
interface RequestCountryInterface extends Application_Admin_ScreenInterface
{
    public const ERROR_NO_COUNTRY_SPECIFIED = 114201;
    public const REQUEST_PARAM_COUNTRY_ID = Application_Countries::REQUEST_PARAM_ID;

    public function getCountry() : ?Application_Countries_Country;
    public function getCountryOrRedirect(string $url='') : Application_Countries_Country;
    public function requireCountry() : Application_Countries_Country;
}
