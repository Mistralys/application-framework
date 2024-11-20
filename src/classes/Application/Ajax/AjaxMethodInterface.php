<?php
/**
 * @package Application
 * @subpackage AJAX
 */

declare(strict_types=1);

namespace Application\Ajax;

/**
 * Interface for AJAX methods. The base implementation is
 * available in {@see \Application_AjaxMethod}.
 *
 * @package Application
 * @subpackage AJAX
 */
interface AjaxMethodInterface
{
    public const RETURN_FORMATS = array(
        AjaxMethodInterface::RETURNFORMAT_HTML,
        AjaxMethodInterface::RETURNFORMAT_JSON,
        AjaxMethodInterface::RETURNFORMAT_TEXT,
        AjaxMethodInterface::RETURNFORMAT_XML
    );

    public const PAYLOAD_STATE = 'state';
    public const STATE_ERROR = 'error';
    public const STATE_SUCCESS = 'success';
    public const ERROR_MALFORMED_JSON_DATA = 554001;
    public const PAYLOAD_ERROR_MESSAGE = 'message';
    public const PAYLOAD_REQUEST_URI = 'request_uri';
    public const PAYLOAD_ERROR_CODE = 'code';
    public const RETURNFORMAT_HTML = 'HTML';
    public const RETURNFORMAT_JSON = 'JSON';
    public const RETURNFORMAT_TEXT = 'TXT';
    public const RETURNFORMAT_XML = 'XML';
    public const PAYLOAD_DATA = 'data';

    public function getMethodName() : string;
    public function getID() : string;
    public function isFormatSupported(string $formatName) : bool;
    public function process(string $formatName) : void;
    public function enableDebug(bool $enable=true) : self;

    /**
     * @return string[]
     */
    public function getSupportedFormats() : array;
}
