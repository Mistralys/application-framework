<?php
/**
 * @package Connectors
 * @see \Connectors\Request\RequestSerializer
 */

declare(strict_types=1);

namespace Connectors\Request;

use Application\Exception\UnexpectedInstanceException;
use Application_Exception;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use Connectors;
use Connectors_Connector;
use Connectors_Exception;
use Connectors_Request;
use Connectors_Request_Method;
use Connectors_Request_URL;
use Connectors_Response;

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
    public const ERROR_UNKNOWN_REQUEST_TYPE = 124101;

    public const KEY_CONNECTOR_ID = 'connectorID';
    public const KEY_URL = 'url';
    public const KEY_ID = 'id';
    public const KEY_POST_DATA = 'postData';
    public const KEY_GET_DATA = 'getData';
    public const KEY_HEADERS = 'headers';
    public const KEY_TIMEOUT = 'timeout';
    public const KEY_HTTP_METHOD = 'HTTPMethod';
    public const REQUEST_TYPE_URL = 'URL';
    public const KEY_REQUEST_TYPE = 'requestType';
    public const REQUEST_TYPE_METHOD = 'Method';

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
            self::KEY_URL => $request->getRequestURL(),
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
        $data = ArrayDataCollection::create(JSONConverter::json2array($json));
        $connector = Connectors::createConnector($data->getString(self::KEY_CONNECTOR_ID));

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
