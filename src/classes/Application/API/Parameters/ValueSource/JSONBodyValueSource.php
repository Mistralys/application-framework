<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\ValueSource;

use Application\API\Traits\JSONRequestInterface;

/**
 * {@see ParamValueSourceInterface} implementation for methods implementing
 * {@see JSONRequestInterface}: resolves raw values from the parsed JSON
 * request body first, falling back to a {@see RequestValueSource} (query
 * string / POST) for any key the body does not carry.
 *
 * The body value always wins when a key is present in both: this lets a
 * `callApi()`-style caller keep sending small parameters as query/POST
 * values while a JSON body carries the rest (e.g. file content), per the
 * plan's Approach §1.
 *
 * The body is read through the method's {@see JSONRequestInterface::getRequestData()}
 * on every call, never cached locally here: that method is itself the
 * lazy, re-entry-guarded, once-per-request parse point (see
 * {@see \Application\API\Traits\JSONRequestTrait::getRequestData()}), so
 * this class stays a thin, side-effect-free view over it.
 *
 * @package API
 * @subpackage Parameters
 */
class JSONBodyValueSource implements ParamValueSourceInterface
{
    private JSONRequestInterface $method;
    private ParamValueSourceInterface $fallback;

    public function __construct(JSONRequestInterface $method, ParamValueSourceInterface $fallback)
    {
        $this->method = $method;
        $this->fallback = $fallback;
    }

    /**
     * @return array<string,mixed>
     */
    private function getBodyData() : array
    {
        return $this->method->getRequestData()->getData();
    }

    public function hasKey(string $name) : bool
    {
        return array_key_exists($name, $this->getBodyData()) || $this->fallback->hasKey($name);
    }

    public function getRawValue(string $name) : mixed
    {
        $data = $this->getBodyData();

        if(array_key_exists($name, $data)) {
            return $data[$name];
        }

        return $this->fallback->getRawValue($name);
    }

    public function getKeys() : array
    {
        return array_values(array_unique(array_merge(
            array_keys($this->getBodyData()),
            $this->fallback->getKeys()
        )));
    }
}
