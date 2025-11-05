<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\API\APIMethodInterface;
use Application\Countries\API\ParamSets\AppCountryParamRule;
use Application_Countries_Country;

/**
 * Trait used to implement API methods that work with countries.
 *
 * ## Usage
 *
 * - Use {@see self::registerAppCountryID()} and/or {@see self::registerAppCountryISO()}
 * - OR
 * - Use {@see self::registerAppCountryParams()} to add an OR rule with both parameters
 * - Use {@see self::resolveAppCountry()} to get the country
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPIInterface
 */
trait AppCountryAPITrait
{
    private ?AppCountryISOParam $paramCountryISO = null;
    private ?AppCountryIDParam $paramCountryID = null;
    private ?AppCountryParamRule $appCountryParamRule = null;
    private ?Application_Countries_Country $selectedAppCountry = null;
    private bool $appCountrySelected = false;

    public function selectAppCountry(Application_Countries_Country $country) : self
    {
        $this->selectedAppCountry = $country;
        return $this;
    }

    protected function registerAppCountryParams() : void
    {
        $this->appCountryParamRule = new AppCountryParamRule($this);
        $this->manageParams()->registerRule($this->appCountryParamRule);
    }

    public function registerAppCountryID() : AppCountryIDParam
    {
        if(isset($this->paramCountryID)) {
            return $this->paramCountryID;
        }

        $this->paramCountryID = new AppCountryIDParam();
        $this->manageParams()->registerParam($this->paramCountryID);
        return $this->paramCountryID;
    }

    public function registerAppCountryISO() : AppCountryISOParam
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
        if($this->appCountrySelected || isset($this->selectedAppCountry)) {
            return $this->selectedAppCountry;
        }

        $this->appCountrySelected = true;

        $this->selectedAppCountry =
            $this->getAppCountryIDParam()?->getCountry()
            ??
            $this->getAppCountryISOParam()?->getCountry()
            ??
            $this->getAppCountryParamRule()?->getCountry();

        return $this->selectedAppCountry;
    }

    public function getAppCountryParamRule() : ?AppCountryParamRule
    {
        return $this->appCountryParamRule;
    }

    public function requireAppCountry() : Application_Countries_Country
    {
        $country = $this->resolveAppCountry();
        if($country !== null) {
            return $country;
        }

        $this->errorResponseBadRequest()
            ->setErrorMessage('No valid app country parameter provided.')
            ->send();
    }
}
