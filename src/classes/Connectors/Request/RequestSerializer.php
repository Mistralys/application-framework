<?php
/**
 * @package Connectors
 * @see \Connectors\Request\RequestSerializer
 */

declare(strict_types=1);

namespace Connectors\Request;

use Application\AppFactory;
use Application\Exception\UnexpectedInstanceException;
use Application_Exception;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use Connectors;
use Connectors_Exception;
use Connectors_Request;
use Connectors_Request_Method;
use Connectors_Request_URL;
use Mistralys\AppFramework\Helpers\JSONUnserializer;

/**
 * Utility class that handles serializing and unserializing
 * a connector request instance.
 *
 * @package Connectors
 * @subpackage Request
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestSerializer
{
    public const int ERROR_UNKNOWN_REQUEST_TYPE = 124101;

    public const string KEY_CONNECTOR_ID = 'connectorID';
    public const string KEY_URL = 'url';
    public const string KEY_ID = 'id';
    public const string KEY_POST_DATA = 'postData';
    public const string KEY_GET_DATA = 'getData';
    public const string KEY_HEADERS = 'headers';
    public const string KEY_TIMEOUT = 'timeout';
    public const string KEY_HTTP_METHOD = 'HTTPMethod';
    public const string REQUEST_TYPE_URL = 'URL';
    public const string KEY_REQUEST_TYPE = 'requestType';
    public const string REQUEST_TYPE_METHOD = 'Method';

    /**
     * @param Connectors_Request $request
     * @return string
     *
     * @throws Connectors_Exception
     * @throws JSONConverterException
     */
    public static function serialize(Connectors_Request $request) : string
    {
        return JSONConverter::var2json(array(
            self::KEY_REQUEST_TYPE => self::resolveRequestType($request),
            self::KEY_CONNECTOR_ID => $request->getConnector()->getID(),
            self::KEY_URL => $request->getBaseURL(),
            self::KEY_ID => $request->getID(),
            self::KEY_POST_DATA => $request->getPostData(),
            self::KEY_GET_DATA => $request->getGetData(),
            self::KEY_HEADERS => $request->getHeaders(),
            self::KEY_TIMEOUT => $request->getTimeout(),
            self::KEY_HTTP_METHOD => $request->getHTTPMethod()
        ));
    }

    public static function resolveRequestType(Connectors_Request $request) : string
    {
        if($request instanceof Connectors_Request_URL)
        {
            return self::REQUEST_TYPE_URL;
        }

        if($request instanceof Connectors_Request_Method)
        {
            return self::REQUEST_TYPE_METHOD;
        }

        throw new Connectors_Exception(
            $request->getConnector(),
            'Unknown request type.',
            sprintf(
                'The request type of class [%s] is unknown.',
                get_class($request)
            ),
            self::ERROR_UNKNOWN_REQUEST_TYPE
        );
    }

    /**
     * @param string $json
     * @return Connectors_Request|NULL
     *
     * @throws JSONConverterException
     * @throws UnexpectedInstanceException
     * @throws Application_Exception
     * @throws Connectors_Exception
     */
    public static function unserialize(string $json) : ?Connectors_Request
    {
        $unserialized = JSONUnserializer::create($json, 'Unserialize cached connector request data.', false)->getData();

        if($unserialized === null)
        {
            return null;
        }

        $data = ArrayDataCollection::create($unserialized);

        $connectorID = $data->getString(self::KEY_CONNECTOR_ID);

        if(!Connectors::connectorExists($connectorID))
        {
            AppFactory::createLogger()->logError(
                'Cannot use connector cache, the connector [%s] specified in the cache data does not exist.',
                $connectorID
            );

            return null;
        }

        $connector = Connectors::createConnector($connectorID);

        $type = $data->getString(self::KEY_REQUEST_TYPE);

        // Old requests did not have the type stored.
        if(empty($type))
        {
            return null;
        }

        if($type === self::REQUEST_TYPE_METHOD)
        {
            $request = new Connectors_Request_Method(
                $connector,
                $data->getString(self::KEY_URL),
                $data->getString(self::KEY_HTTP_METHOD),
                $data->getArray(self::KEY_POST_DATA),
                $data->getArray(self::KEY_GET_DATA),
                $data->getString(self::KEY_ID)
            );
        }
        else if($type === self::REQUEST_TYPE_URL)
        {
            $request = new Connectors_Request_URL(
                $connector,
                $data->getString(self::KEY_URL),
                $data->getArray(self::KEY_POST_DATA),
                $data->getArray(self::KEY_GET_DATA),
                $data->getString(self::KEY_ID)
            );
        }
        else
        {
            throw new Connectors_Exception(
                $connector,
                'Unknown request type.',
                sprintf(
                    'The request type [%s] is unknown.',
                    $type
                ),
                self::ERROR_UNKNOWN_REQUEST_TYPE
            );
        }

        $headers = $data->getArray(self::KEY_HEADERS);

        foreach ($headers as $name => $value)
        {
            $request->setHeader($name, $value);
        }

        $request->setTimeout($data->getInt(self::KEY_TIMEOUT));
        $request->setHTTPMethod($data->getString(self::KEY_HTTP_METHOD));

        return $request;
    }
}
