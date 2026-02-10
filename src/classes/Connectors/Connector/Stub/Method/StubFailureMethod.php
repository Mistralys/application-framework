<?php
/**
 * File containing the {@link \Connectors\Connector\Stub\Method\StubFailureMethod} class.
 *
 * @package Connectors
 * @subpackage Dummy
 * @see \Connectors\Connector\Stub\Method\StubFailureMethod
 */

declare(strict_types=1);

namespace Connectors\Connector\Stub\Method;

use Connectors\Connector\ConnectorException;
use Connectors_Connector_Method_Get;

/**
 * Pigeon API method: Retrieves all words available in the
 * dictionary (placeholders for global information, like
 * phone numbers).
 *
 * @package Connectors
 * @subpackage Stub
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class StubFailureMethod extends Connectors_Connector_Method_Get
{
    public const int ERROR_CONNECTION_DID_NOT_FAIL = 70101;
    public const int ERROR_CONNECTION_FAILED = 70102;

    /**
     * @return never
     * @throws ConnectorException
     */
    public function failFetchData()
    {
        $response = $this->executeRequestByName('somehwere');

        if (!$response->isError()) {
            throw new ConnectorException(
                $this->connector,
                'Request did not fail as expected.',
                '',
                self::ERROR_CONNECTION_DID_NOT_FAIL
            );
        }

        throw $response->createException(
            'Request failed as expected.',
            self::ERROR_CONNECTION_FAILED
        );
    }
}
