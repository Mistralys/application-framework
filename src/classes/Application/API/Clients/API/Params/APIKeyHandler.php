<?php
/**
 * @package API Clients
 * @subpackage API Parameters
 */

declare(strict_types=1);

namespace Application\API\Clients\API\Params;

use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Parameters\Handlers\BaseParamHandler;

/**
 * Handler for the API Key parameter: Utility class that
 * handles registration, selection, and resolution of the
 * API Key parameter {@see APIKeyParam}.
 *
 * @package API Clients
 * @subpackage API Parameters
 *
 * @method APIKeyParam register()
 * @method APIKeyParam|null getParam()
 */
class APIKeyHandler extends BaseParamHandler
{
    protected function createParam(): APIKeyParam
    {
        return new APIKeyParam();
    }

    public function selectValue(mixed $value): self
    {
        if($value instanceof APIKeyRecord) {
            parent::selectValue($value);
        } else {
            parent::selectValue(null);
        }

        return $this;
    }

    public function selectKey(APIKeyRecord $key) : self
    {
        return $this->selectValue($key);
    }

    public function getKey() : ?APIKeyRecord
    {
        $key = $this->resolveValue();

        if($key instanceof APIKeyRecord) {
            return $key;
        }

        return null;
    }

    protected function resolveValueFromSubject(): ?APIKeyRecord
    {
        return $this->getParam()?->getKey();
    }
}
