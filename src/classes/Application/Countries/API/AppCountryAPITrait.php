<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application_Countries_Country;

/**
 * Trait used to implement API methods that work with countries.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPIInterface
 */
trait AppCountryAPITrait
{
    private ?AppCountryISOParam $paramCountryISO = null;
    private ?AppCountryIDParam $paramCountryID = null;

    protected function registerAppCountryID() : AppCountryIDParam
    {
        if(isset($this->paramCountryID)) {
            return $this->paramCountryID;
        }

        $this->paramCountryID = new AppCountryIDParam();
        $this->manageParams()->registerParam($this->paramCountryID);
        return $this->paramCountryID;
    }

    protected function registerAppCountryISO() : AppCountryISOParam
    {
        if(isset($this->paramCountryISO)) {
            return $this->paramCountryISO;
        }

        $this->paramCountryISO = new AppCountryISOParam();
        $this->manageParams()->registerParam($this->paramCountryISO);
        return $this->paramCountryISO;
    }

    public function getAppCountryIDParam() : ?AppCountryIDParam
    {
        return $this->paramCountryID;
    }

    public function getAppCountryISOParam() : ?AppCountryISOParam
    {
        return $this->paramCountryISO;
    }

    public function resolveAppCountry() : ?Application_Countries_Country
    {
        return
            $this->getAppCountryIDParam()?->getCountry()
            ??
            $this->getAppCountryISOParam()?->getCountry()
            ??
            null;
    }

    public function requireAppCountry() : Application_Countries_Country
    {
        $country = $this->resolveAppCountry();
        if($country !== null) {
            return $country;
        }

        $this->errorResponse(AppCountryAPIInterface::ERROR_INVALID_REQUEST_PARAMS)
            ->makeBadRequest()
            ->send();
    }
}
