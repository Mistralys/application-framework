<?php
/**
 * @package API
 * @subpackage Documentation
 */

declare(strict_types=1);

namespace Application\API\Documentation\Examples;

use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use AppUtils\ConvertHelper\JSONConverter;
use UI_Renderable;

/**
 * Renders an example JSON response for a given API method implementing {@see JSONResponseInterface},
 * for use in API documentation. It is used by {@see JSONResponseTrait::renderExample()} to fetch
 * the JSON to use.
 *
 * @package API
 * @subpackage Documentation
 */
class JSONMethodExample extends UI_Renderable
{
    private JSONResponseInterface $method;

    public function __construct(JSONResponseInterface $method)
    {
        $this->method = $method;

        parent::__construct();
    }

    protected function _render(): string
    {
        $output = '<pre>'.JSONConverter::var2json($this->method->getExampleJSONResponse(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).'</pre>';

        $keys = $this->method->getReponseKeyDescriptions();

        ksort($keys);

        if(!empty($keys)) {
            $output .= '<h3>'.t('Response keys explained').'</h3>';
            $output .= '<ul class="api-response-keys">';
            foreach($keys as $key => $desc) {
                $output .= '<li><strong>'.htmlspecialchars($key).'</strong>: '.htmlspecialchars($desc).'</li>';
            }
            $output .= '</ul>';
        }

        return $output;
    }
}
