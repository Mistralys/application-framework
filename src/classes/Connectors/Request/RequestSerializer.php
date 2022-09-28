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
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use Connectors;
use Connectors_Exception;
use Connectors_Request;
use Connectors_Request_URL;

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
    public const KEY_CONNECTOR_ID = 'connectorID';
    public const KEY_URL = 'url';
    public const KEY_ID = 'id';
    public const KEY_POST_DATA = 'postData';
    public const KEY_GET_DATA = 'getData';
    public const KEY_HEADERS = 'headers';
    public const KEY_TIMEOUT = 'timeout';
    public const KEY_HTTP_METHOD = 'HTTPMethod';

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

    /**
     * @param string $json
     * @return Connectors_Request
     *
     * @throws JSONConverterException
     * @throws UnexpectedInstanceException
     * @throws Application_Exception
     * @throws Connectors_Exception
     */
    public static function unserialize(string $json) : Connectors_Request
    {
        $data = ArrayDataCollection::create(JSONConverter::json2array($json));

        $request = new Connectors_Request_URL(
            Connectors::createConnector($data->getString(self::KEY_CONNECTOR_ID)),
            $data->getString(self::KEY_URL),
            $data->getArray(self::KEY_POST_DATA),
            $data->getArray(self::KEY_GET_DATA),
            $data->getString(self::KEY_ID)
        );

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
