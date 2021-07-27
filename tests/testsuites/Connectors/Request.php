<?php

use PHPUnit\Framework\TestCase;

class Connectors_RequestTest extends TestCase
{
    private $requestClass;

    public function setUp() : void
    {
        $connector = new Connectors_Connector_Dummy();
        $this->requestClass = new Connectors_Request_URL($connector, '');
    }

    public function test_isValidResponseCode_Valid()
    {
        // Method: GET Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_GET);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: GET Response Code:200');

        // Method: DELETE Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: DELETE Response Code:200');

        // Method: DELETE Response Code:204
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(204);
        $this->assertTrue($isValid, 'Method: DELETE Response Code:204');

        // Method: PUT Response Code:201
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(201);
        $this->assertTrue($isValid, 'Method: PUT Response Code:201');

        // Method: PUT Response Code:202
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(202);
        $this->assertTrue($isValid, 'Method: PUT Response Code:202');

        // Method: PUT Response Code:200
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(200);
        $this->assertTrue($isValid, 'Method: PUT Response Code:200');

        // Method: PUT Response Code:202
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(202);
        $this->assertTrue($isValid, 'Method: PUT Response Code:202');
    }

    public function test_isValidResponseCode_Invalid()
    {
        // Method: GET Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_GET);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: GET Response Code:300');

        // Method: DELETE Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_DELETE);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: DELETE Response Code:300');

        // Method: PUT Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_PUT);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: PUT Response Code:300');

        // Method: POST Response Code:300
        $this->requestClass->setHTTPMethod(HTTP_Request2::METHOD_POST);
        $isValid = $this->requestClass->isValidResponseCode(300);
        $this->assertFalse($isValid, 'Method: POST Response Code:300');
    }
}
