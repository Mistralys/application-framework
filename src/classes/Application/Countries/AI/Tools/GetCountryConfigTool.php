<?php
/**
 * @package Countries
 * @subpackage AI Tools
 */

declare(strict_types=1);

namespace Application\Countries\AI\Tools;

use Application\AI\Cache\AICacheStrategyInterface;
use Application\AI\Cache\Strategies\FixedDurationStrategy;
use Application\AI\Tools\BaseAITool;
use Application\AppFactory;
use Application\Countries\AITools\CountryAIException;
use Application\Countries\API\Methods\GetAppCountriesAPI;

/**
 * @package Countries
 * @subpackage AI Tools
 */
class GetCountryConfigTool extends BaseAITool
{
    public const string TOOL_NAME = 'get_country_configuration';
    public const string TOOL_DESCRIPTION = "Get detailed currency and locale config for a specific country.";

    protected string $isoCode;

    public function __construct(string $isoCode)
    {
        $this->isoCode = $isoCode;
    }

    public function getID(): string
    {
        return self::TOOL_NAME;
    }

    public function execute(): array
    {
        $countries = AppFactory::createCountries();
        if($countries->isoExists($this->isoCode)) {
            return GetAppCountriesAPI::collectCountry($countries->getByISO($this->isoCode));
        }

        throw new CountryAIException(
            "Country with ISO code '{$this->isoCode}' does not exist or is not supported.",
            CountryAIException::ERROR_INVALID_COUNTRY
        );
    }

    public function getCacheStrategy(): AICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_24_HOURS);
    }
}

