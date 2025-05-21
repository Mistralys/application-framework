<?php
/**
 * @package Countries
 * @subpackage Rights
 */

declare(strict_types=1);

namespace Application\Countries\Rights;

use Application_User_Rights_Group;

/**
 * Trait used to configure the country rights setup
 * in the user class.
 *
 * @package Countries
 * @subpackage Rights
 * @see CountryRightsInterface
 */
trait CountryRightsTrait
{
    public function canEditCountries() : bool { return $this->can(CountryRightsInterface::RIGHT_EDIT_COUNTRIES); }
    public function canDeleteCountries() : bool { return $this->can(CountryRightsInterface::RIGHT_DELETE_COUNTRIES); }
    public function canCreateCountries() : bool { return $this->can(CountryRightsInterface::RIGHT_CREATE_COUNTRIES); }
    public function canViewCountries() : bool { return $this->can(CountryRightsInterface::RIGHT_VIEW_COUNTRIES); }

    private function registerCountryRights(Application_User_Rights_Group $group) : void
    {
        $group->registerRight(CountryRightsInterface::RIGHT_DELETE_COUNTRIES, t('Delete countries'))
            ->actionDelete()
            ->grantRight(CountryRightsInterface::RIGHT_CREATE_COUNTRIES);

        $group->registerRight(CountryRightsInterface::RIGHT_CREATE_COUNTRIES, t('Add countries'))
            ->actionCreate()
            ->grantRight(CountryRightsInterface::RIGHT_EDIT_COUNTRIES);

        $group->registerRight(CountryRightsInterface::RIGHT_EDIT_COUNTRIES, t('Edit countries'))
            ->actionEdit()
            ->grantRight(CountryRightsInterface::RIGHT_VIEW_COUNTRIES);

        $group->registerRight(CountryRightsInterface::RIGHT_VIEW_COUNTRIES, t('View countries'))
            ->actionView();
    }
}
