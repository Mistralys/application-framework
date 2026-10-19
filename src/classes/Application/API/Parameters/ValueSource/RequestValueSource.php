<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\ValueSource;

use Application\AppFactory;
use Application_Request;

/**
 * Default {@see ParamValueSourceInterface} implementation: resolves raw
 * values from the application's request (query string / POST / `$_REQUEST`).
 *
 * {@see self::getRawValue()} reads directly from {@see Application_Request::getParams()}
 * (the underlying `$_REQUEST` array) rather than through
 * `registerParam($name)->get()`: the latter collapses an empty string to
 * `null`, which every already-migrated concrete parameter type treats as
 * equivalent to an absent value in its own {@see \Application\API\Parameters\BaseAPIParameter::resolveValue()}
 * anyway (so this is behaviourally neutral for them) — but it would
 * collapse exactly the absent/empty distinction
 * {@see \Application\API\Parameters\Type\ClearableStringParameter} depends
 * on. This preserves the pre-value-source-seam behaviour for every other
 * type while making this source usable by a clearable parameter too.
 *
 * @package API
 * @subpackage Parameters
 */
class RequestValueSource implements ParamValueSourceInterface
{
    private Application_Request $request;

    public function __construct(?Application_Request $request=null)
    {
        $this->request = $request ?? AppFactory::createRequest();
    }

    public function hasKey(string $name) : bool
    {
        return array_key_exists($name, $this->request->getParams());
    }

    public function getRawValue(string $name) : mixed
    {
        return $this->request->getParams()[$name] ?? null;
    }

    public function getKeys() : array
    {
        return array_keys($this->request->getParams());
    }
}
