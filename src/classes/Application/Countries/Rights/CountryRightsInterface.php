<?php
/**
 * @package Countries
 * @subpackage Rights
 */

declare(strict_types=1);

namespace Application\Countries\Rights;

use Application_User_Interface;

/**
 * Interface defining all user rights used by the country management.
 * The user configuration is done in the trait {@see CountryRightsTrait}.
 *
 * @package Countries
 * @subpackage Rights
 * @see CountryRightsTrait
 */
interface CountryRightsInterface extends Application_User_Interface
{
    public const RIGHT_VIEW_COUNTRIES = 'ViewCountries';
    public const RIGHT_CREATE_COUNTRIES = 'CreateCountries';
    public const RIGHT_EDIT_COUNTRIES = 'EditCountries';
    public const RIGHT_DELETE_COUNTRIES = 'DeleteCountries';

    public function canViewCountries() : bool;
    public function canEditCountries() : bool;
    public function canDeleteCountries() : bool;
    public function canCreateCountries() : bool;
}
