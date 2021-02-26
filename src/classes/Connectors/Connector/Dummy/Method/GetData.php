<?php
/**
 * File containing the {@link Connectors_Connector_Dummy_Method_GetData} class.
 *
 * @package Connectors
 * @subpackage Dummy
 * @see Connectors_Connector_Dummy_Method_GetData
 */

declare(strict_types=1);

/**
 * Pigeon API method: Retrieves all words available in the 
 * dictionary (placeholders for global information, like
 * phone numbers).
 *
 * @package Connectors
 * @subpackage Dummy
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Connectors_Connector_Dummy_Method_GetData extends Connectors_Connector_Method_Get
{
    const ERROR_CONNECTION_DID_NOT_FAIL = 70101;
    const ERROR_CONNECTION_FAILED = 70102;

    public function getData() : array
    {
        $response = $this->executeRequestByName('somehwere');
     
        if(!$response->isError())
        {
            throw new Connectors_Exception(
                $this->connector,
                'Request did not fail as expected.',
                '',
                self::ERROR_CONNECTION_DID_NOT_FAIL
            );
        }

        throw $response->createException(
            'Request failed as expeced.',
            self::ERROR_CONNECTION_FAILED
        );
    }
}
