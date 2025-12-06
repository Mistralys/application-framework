<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Flavors;

use Application\API\Clients\Keys\APIKeyException;

/**
 * Trait used to implement parameters that are always required
 * and cannot be made optional.
 *
 * @package API
 * @subpackage Parameters
 */
trait RequiredOnlyParamTrait
{
    /**
     * @param bool $required
     * @return $this
     * @throws APIKeyException
     */
    public function makeRequired(bool $required = true): self
    {
        if($required !== true) {
            throw new APIKeyException(
                'The API Key parameter is always required and cannot be made optional.',
                '',
                APIKeyException::API_KEY_PARAM_CANNOT_BE_OPTIONAL
            );
        }

        return parent::makeRequired();
    }

    public function isRequired(): bool
    {
        return true;
    }
}
