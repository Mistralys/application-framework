<?php
/**
 * @package Connectors
 * @see ConnectorException
 */

declare(strict_types=1);

namespace Connectors\Connector;

use AppUtils\ConvertHelper;
use Connectors\ConnectorsException;
use Connectors_Request;
use Connectors_Response;
use HTTP_Request2_Response;
use JsonException;
use Throwable;

/**
 * Connector-specific exception, which gives access to all
 * available information, from the request to the response.
 *
 * @package Connectors
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConnectorException extends ConnectorsException
{
    public const int ERROR_NO_ACTIVE_RESPONSE_AVAILABLE = 42401;
    protected ConnectorInterface $connector;
    protected ?HTTP_Request2_Response $response = null;
    protected ?Connectors_Request $request = null;
    private ?Connectors_Response $connectorResponse = null;

    public function __construct(ConnectorInterface $connector, string $message, string $developerInfo = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $developerInfo, $code, $previous);

        $this->connector = $connector;
    }

    public function getConnector(): ConnectorInterface
    {
        return $this->connector;
    }

    public function setRequest(Connectors_Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function hasRequest(): bool
    {
        return isset($this->request);
    }

    public function getRequest(): ?Connectors_Request
    {
        return $this->request;
    }

    /**
     * @param HTTP_Request2_Response $response
     * @return $this
     */
    public function setResponse(HTTP_Request2_Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function hasResponse(): bool
    {
        return isset($this->response);
    }

    public function getResponse(): ?HTTP_Request2_Response
    {
        return $this->response;
    }

    public function setConnectorResponse(Connectors_Response $response): self
    {
        $this->connectorResponse = $response;
        return $this;
    }

    public function getConnectorResponse(): ?Connectors_Response
    {
        return $this->connectorResponse;
    }

    public function hasConnectorResponse(): bool
    {
        return isset($this->connectorResponse);
    }

    public function getDeveloperInfo(): string
    {
        $lines = array();
        $details = parent::getDeveloperInfo();

        if (!empty($details)) {
            $lines[] = $details;
        }

        if (isset($this->response)) {
            $lines[] = sprintf('Requested URL: [%1$s]', $this->response->getEffectiveUrl());
            $lines[] = sprintf('Response status code: [%1$s]', $this->response->getStatus());
            $lines[] = sprintf('Response status message: [%1$s].', $this->response->getReasonPhrase());
            $lines[] = '';
            $lines[] = 'Response headers:';

            $headers = $this->response->getHeader();
            foreach ($headers as $name => $value) {
                $lines[] = $name . ' = ' . $value;
            }

            $lines[] = '';
            $lines[] = 'Response body:';
            $lines[] = $this->parseBody($this->response->getBody());
            $lines[] = '';
        }

        if (isset($this->request)) {
            $lines[] = sprintf('Request method: [%s]', $this->request->getHTTPMethod());
            $lines[] = '';
            $lines[] = 'Request headers:';

            $headers = $this->request->getHeaders();
            foreach ($headers as $name => $value) {
                $lines[] = $name . ' = ' . $value;
            }

            $body = $this->request->getBody();

            $lines[] = 'Request body:';
            $lines[] = $this->parseBody($body);
            $lines[] = '';

            $lines[] = 'Request variables:';
            $data = $this->request->getPostData();
            foreach ($data as $key => $val) {
                $lines[] = $key . ' = ' . $val;
            }

            $lines[] = '';
        }

        if (isCLI()) {
            return implode(PHP_EOL, $lines);
        }

        return implode('<br>', $lines);
    }

    protected function parseBody(string $source): string
    {
        $source = trim($source);

        if (empty($source)) {
            return '(empty string)';
        }

        if ($source[0] !== '{') {
            return $this->pre($source);
        }

        try {
            $data = json_decode($source, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($data)) {
                $source = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
                return $this->pre($source);
            }
        } catch (JsonException $e) {
        }

        return $this->pre($source);
    }

    protected function pre(string $text): string
    {
        if (isCLI()) {
            return $text;
        }

        return ConvertHelper::print_r($text, true);
    }
}
