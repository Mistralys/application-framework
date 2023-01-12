<?php
/**
 * @package Connectors
 * @subpackage Response
 * @see \Connectors\Response\ResponseSerializer
 */

declare(strict_types=1);

namespace Connectors\Response;

use Application;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use Connectors_Request;
use Connectors_Response;
use HTTP_Request2_Response;

/**
 * Utility class that handles serializing and unserializing
 * a connector response instance.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ResponseSerializer
{
    public const KEY_STATUS_CODE = 'statusCode';
    public const KEY_STATUS_MESSAGE = 'statusMessage';
    public const KEY_BODY = 'body';
    public const KEY_REQUEST = 'request';

    public static function serialize(Connectors_Response $response) : string
    {
        return ConvertHelper::var2json(array(
            self::KEY_STATUS_CODE => $response->getStatusCode(),
            self::KEY_STATUS_MESSAGE => $response->getStatusMessage(),
            self::KEY_BODY => $response->getRawJSON(),
            self::KEY_REQUEST => $response->getRequest()->serialize()
        ));
    }

    public static function unserialize(string $serialized) : ?Connectors_Response
    {
        $data = ArrayDataCollection::create(JSONConverter::json2array($serialized));

        $request = Connectors_Request::unserialize($data->getString(self::KEY_REQUEST));
        if($request === null)
        {
            return null;
        }

        $logPrefix = $request->getLogIdentifier().' | Response | ';

        Application::log(sprintf(
            '%sCreating from serialized data. Status: [%s %s]. URL: [%s].',
            $logPrefix,
            $data->getInt(self::KEY_STATUS_CODE),
            $data->getString(self::KEY_STATUS_MESSAGE),
            $request->getRequestURL()
        ));

        $response = new HTTP_Request2_Response(
            sprintf(
                'HTTP/1.0 %s %s',
                $data->getInt(self::KEY_STATUS_CODE),
                $data->getString(self::KEY_STATUS_MESSAGE)
            ),
            false,
            $request->getRequestURL()
        );

        // Use the original response's body - the response object
        // will interpret it just like it did the original response.
        $response->appendBody($data->getString(self::KEY_BODY));

        return new Connectors_Response($request, $response);
    }
}
