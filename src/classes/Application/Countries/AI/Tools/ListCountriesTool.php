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

/**
 * @package Countries
 * @subpackage AI Tools
 */
class ListCountriesTool extends BaseAITool
{
    public const string TOOL_NAME = 'list_supported_countries';
    public const string TOOL_DESCRIPTION = 'Returns a complete list of all supported/available countries with their ISO codes. Use this when the user asks: what/which countries are available, show all countries, list supported countries, or wants to see country options.';

    public function getID(): string
    {
        return self::TOOL_NAME;
    }

    public function getCacheStrategy(): AICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_24_HOURS);
    }

    public function execute(): array
    {
        $result = array();

        foreach(AppFactory::createCountries()->getAll() as $country) {
            $result[] = [
                'isoCode' => $country->getISO(),
                'name' => $country->getLabel()
            ];
        }

        return $result;
    }
}
