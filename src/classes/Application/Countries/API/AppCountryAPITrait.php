<?php
/**
 * @package Countries
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Countries\API;

use Application\Countries\API\Params\AppCountryIDParam;
use Application\Countries\API\Params\AppCountryISOParam;
use Application\Countries\API\ParamSets\AppCountryParamRule;
use Application_Countries_Country;

/**
 * Trait used to implement API methods that work with countries.
 *
 * ## Usage
 *
 * Use {@see self::manageAppCountryParams()} to manage the country parameters.
 *
 * @package Countries
 * @subpackage API
 * @see AppCountryAPIInterface
 */
trait AppCountryAPITrait
{
    private ?AppCountryParamsContainer $appCountryParamsContainer = null;

    public function manageAppCountryParams() : AppCountryParamsContainer
    {
        if(!isset($this->appCountryParamsContainer)) {
            $this->appCountryParamsContainer = new AppCountryParamsContainer($this);
        }

        return $this->appCountryParamsContainer;
    }
}
