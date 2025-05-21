<?php
/**
 * @package Countries
 * @subpackage Rights
 */

declare(strict_types=1);

namespace Application\Countries\Rights;

/**
 * User rights used by the country management screens.
 *
 * @package Countries
 * @subpackage Rights
 */
class CountryScreenRights
{
    public const SCREEN_AREA = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
    public const SCREEN_LIST = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
    public const SCREEN_LIST_MULTI_DELETE = CountryRightsInterface::RIGHT_DELETE_COUNTRIES;
    public const SCREEN_VIEW = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
    public const SCREEN_STATUS = CountryRightsInterface::RIGHT_VIEW_COUNTRIES;
    public const SCREEN_CREATE = CountryRightsInterface::RIGHT_CREATE_COUNTRIES;
    public const SCREEN_SETTINGS = CountryRightsInterface::RIGHT_EDIT_COUNTRIES;
}
