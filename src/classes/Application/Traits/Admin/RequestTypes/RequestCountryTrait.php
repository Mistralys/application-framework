<?php
/**
 * @package Application
 * @subpackage Traits
 * @see \Application\Traits\Admin\RequestTypes\RequestCountryTrait
 */

declare(strict_types=1);

namespace Application\Traits\Admin\RequestTypes;

use Application\Admin\ScreenException;
use Application\Interfaces\Admin\RequestTypes\RequestCountryInterface;
use Application_Countries;
use Application_Countries_Country;

/**
 * Trait for classes that have access to a single country,
 * with utility methods to fetch the country instance.
 *
 * ## USAGE
 *
 * - Use the trait.
 * - Implement the interface {@see RequestCountryInterface}.
 * - If not fetching the country from the request (default),
 *   override {@see RequestCountryTrait::resolveCountry()}.
 * - Consider overriding {@see RequestCountryTrait::resolveDefaultCountryRedirectURL()}.
 *
 * @package Application
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RequestCountryInterface
 */
trait RequestCountryTrait
{
    private ?Application_Countries_Country $resolvedCountry = null;

    public function getCountry() : ?Application_Countries_Country
    {
        if(!isset($this->resolvedCountry))
        {
            $this->resolvedCountry = $this->resolveCountry();
        }

        return $this->resolvedCountry;
    }

    public function getCountryOrRedirect(string $url='') : Application_Countries_Country
    {
        $area = $this->getCountry();

        if($area !== null)
        {
            return $area;
        }

        return $this->getAdminScreen()->redirectWithErrorMessage(
            t('No country has been specified.'),
            $this->resolveCountryRedirectURL($url)
        );
    }

    protected function resolveCountryRedirectURL(string $url) : string
    {
        if(!empty($url))
        {
            return $url;
        }

        return $this->resolveDefaultCountryRedirectURL();
    }

    public function requireCountry() : Application_Countries_Country
    {
        $area = $this->getCountry();

        if($area !== null)
        {
            return $area;
        }

        throw new ScreenException(
            $this->getAdminScreen(),
            'No country has been specified.',
            '',
            RequestCountryInterface::ERROR_NO_COUNTRY_SPECIFIED
        );
    }

    protected function resolveCountry() : ?Application_Countries_Country
    {
        return Application_Countries::getInstance()->getByRequest();
    }

    protected function resolveDefaultCountryRedirectURL() : string
    {
        return APP_URL;
    }
}
