<?php
/**
 * @package Countries
 * @subpackage AI Tools
 */

declare(strict_types=1);

namespace Application\Countries\AITools;

use Application\AI\BaseAIToolContainer;
use Application\Countries\AI\Tools\GetCountryConfigTool;
use Application\Countries\AI\Tools\ListCountriesTool;
use PhpMcp\Server\Attributes\McpTool;

/**
 * @package Countries
 * @subpackage AI Tools
 */
class CountryAITools extends BaseAIToolContainer
{
    #[McpTool(name: ListCountriesTool::TOOL_NAME, description: ListCountriesTool::TOOL_DESCRIPTION)]
    public function listCountries(): array
    {
        return $this->runTool(ListCountriesTool::class);
    }

    /**
     * @param string $isoCode Two-letter country code, e.g. `at`, `gb`. Case-insensitive.
     *         The code `uk` is accepted as alias for `gb`, but will exclusively be
     *         referred to as `gb` in the returned data.
     */
    #[McpTool(name: GetCountryConfigTool::TOOL_NAME, description: GetCountryConfigTool::TOOL_DESCRIPTION)]
    public function getCountryConfig(string $isoCode): array
    {
        return $this->runTool(new GetCountryConfigTool($isoCode));
    }
}
