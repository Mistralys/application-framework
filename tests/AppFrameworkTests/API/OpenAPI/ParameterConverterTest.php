<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\OpenAPI\ParameterConverter;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\ValueLookup\SelectableParamValue;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Traits\JSONRequestInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see ParameterConverter}.
 *
 * Uses PHPUnit mocks for all framework objects to keep tests isolated
 * from the application bootstrap.
 */
final class ParameterConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Creates a mock APIParameterInterface with simple scalar returns.
     *
     * @param string $name
     * @param string $typeLabel
     * @param bool $required
     * @param string $description
     * @param mixed $defaultValue
     * @return APIParameterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createParamMock(
        string $name = 'testParam',
        string $typeLabel = 'String',
        bool $required = false,
        string $description = '',
        mixed $defaultValue = null
    ) : APIParameterInterface {
        $mock = $this->createMock(APIParameterInterface::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getTypeLabel')->willReturn($typeLabel);
        $mock->method('isRequired')->willReturn($required);
        $mock->method('getDescription')->willReturn($description);
        $mock->method('hasDescription')->willReturn($description !== '');
        $mock->method('getDefaultValue')->willReturn($defaultValue);
        return $mock;
    }

    /**
     * Creates a mock APIMethodInterface (default: RequestRequestInterface, not JSON body).
     *
     * @return APIMethodInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQueryMethodMock() : APIMethodInterface
    {
        return $this->createMock(APIMethodInterface::class);
    }

    /**
     * Creates a mock method implementing JSONRequestInterface (body params).
     *
     * @return JSONRequestInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createJsonMethodMock() : JSONRequestInterface
    {
        return $this->createMock(JSONRequestInterface::class);
    }

    // -------------------------------------------------------------------------
    // Query parameter conversion
    // -------------------------------------------------------------------------

    public function test_convertParameter_queryParam_hasInQuery() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock());

        $this->assertIsArray($result);
        $this->assertSame('query', $result['in']);
    }

    public function test_convertParameter_queryParam_hasName() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('myParam'));

        $this->assertIsArray($result);
        $this->assertSame('myParam', $result['name']);
    }

    public function test_convertParameter_queryParam_required() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', true));

        $this->assertIsArray($result);
        $this->assertTrue($result['required']);
    }

    public function test_convertParameter_queryParam_notRequired() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', false));

        $this->assertIsArray($result);
        $this->assertFalse($result['required']);
    }

    public function test_convertParameter_queryParam_hasSchema() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'Integer'));

        $this->assertIsArray($result);
        $this->assertArrayHasKey('schema', $result);
        $this->assertSame('integer', $result['schema']['type']);
        $this->assertSame('int64', $result['schema']['format']);
    }

    public function test_convertParameter_queryParam_descriptionIncluded() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', false, 'A test description'));

        $this->assertIsArray($result);
        $this->assertSame('A test description', $result['description']);
    }

    public function test_convertParameter_queryParam_noDescriptionOmitsKey() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', false, ''));

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('description', $result);
    }

    public function test_convertParameter_queryParam_defaultValueInSchema() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', false, '', 'default-val'));

        $this->assertIsArray($result);
        $this->assertSame('default-val', $result['schema']['default']);
    }

    public function test_convertParameter_queryParam_noDefaultOmitsKey() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('p', 'String', false, '', null));

        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('default', $result['schema']);
    }

    // -------------------------------------------------------------------------
    // Header parameter conversion
    // -------------------------------------------------------------------------

    public function test_convertParameter_headerParam_hasInHeader() : void
    {
        $headerParam = $this->createMock(APIHeaderParameterInterface::class);
        $headerParam->method('getName')->willReturn('X-API-Key');
        $headerParam->method('getTypeLabel')->willReturn('String');
        $headerParam->method('isRequired')->willReturn(true);
        $headerParam->method('hasDescription')->willReturn(false);
        $headerParam->method('getDefaultValue')->willReturn(null);

        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($headerParam);

        $this->assertIsArray($result);
        $this->assertSame('header', $result['in']);
        $this->assertSame('X-API-Key', $result['name']);
    }

    public function test_convertParameter_headerParam_onJsonMethod_stillHeader() : void
    {
        // Header params are always header even on JSON body methods
        $headerParam = $this->createMock(APIHeaderParameterInterface::class);
        $headerParam->method('getName')->willReturn('X-Token');
        $headerParam->method('getTypeLabel')->willReturn('String');
        $headerParam->method('isRequired')->willReturn(false);
        $headerParam->method('hasDescription')->willReturn(false);
        $headerParam->method('getDefaultValue')->willReturn(null);

        $converter = new ParameterConverter($this->createJsonMethodMock());
        $result = $converter->convertParameter($headerParam);

        $this->assertIsArray($result);
        $this->assertSame('header', $result['in']);
    }

    // -------------------------------------------------------------------------
    // JSON request body parameter conversion
    // -------------------------------------------------------------------------

    public function test_convertParameter_jsonBodyParam_noInKey() : void
    {
        $converter = new ParameterConverter($this->createJsonMethodMock());
        $result = $converter->convertParameter($this->createParamMock('bodyParam'));

        // Body schema properties do not have 'name', 'in', or 'required' at top level
        $this->assertIsArray($result);
        $this->assertArrayNotHasKey('in', $result);
        $this->assertArrayNotHasKey('name', $result);
        $this->assertArrayNotHasKey('required', $result);
    }

    public function test_convertParameter_jsonBodyParam_hasType() : void
    {
        $converter = new ParameterConverter($this->createJsonMethodMock());
        $result = $converter->convertParameter($this->createParamMock('bodyParam', 'Boolean'));

        $this->assertIsArray($result);
        $this->assertSame('boolean', $result['type']);
    }

    public function test_convertParameter_jsonBodyParam_descriptionInSchema() : void
    {
        $converter = new ParameterConverter($this->createJsonMethodMock());
        $result = $converter->convertParameter($this->createParamMock('bodyParam', 'String', false, 'Body description'));

        $this->assertIsArray($result);
        $this->assertSame('Body description', $result['description']);
    }

    // -------------------------------------------------------------------------
    // Selectable values (enum)
    // -------------------------------------------------------------------------

    public function test_convertParameter_selectableValues_producesEnum() : void
    {
        $selectableMock = $this->createMock(SelectableValueParamInterface::class);
        $selectableMock->method('getName')->willReturn('status');
        $selectableMock->method('getTypeLabel')->willReturn('String');
        $selectableMock->method('isRequired')->willReturn(false);
        $selectableMock->method('hasDescription')->willReturn(false);
        $selectableMock->method('getDefaultValue')->willReturn(null);
        $selectableMock->method('getSelectableValues')->willReturn(array(
            new SelectableParamValue('active', 'Active'),
            new SelectableParamValue('inactive', 'Inactive'),
        ));

        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($selectableMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('schema', $result);
        $this->assertSame(array('active', 'inactive'), $result['schema']['enum']);
    }

    // -------------------------------------------------------------------------
    // Reserved parameters
    // -------------------------------------------------------------------------

    public function test_convertParameter_reservedParam_method_returnsNull() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('method'));

        $this->assertNull($result);
    }

    public function test_convertParameter_reservedParam_apiVersion_returnsNull() : void
    {
        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameter($this->createParamMock('apiVersion'));

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Batch conversion
    // -------------------------------------------------------------------------

    public function test_convertParameters_returnsAllThreeKeys() : void
    {
        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array());

        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameters($manager);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertArrayHasKey('requestBodyProperties', $result);
        $this->assertArrayHasKey('requiredBodyProperties', $result);
    }

    public function test_convertParameters_queryMethod_allParamsGoToParameters() : void
    {
        $param1 = $this->createParamMock('foo', 'String', true);
        $param2 = $this->createParamMock('bar', 'Integer', false);

        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array($param1, $param2));

        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameters($manager);

        $this->assertCount(2, $result['parameters']);
        $this->assertEmpty($result['requestBodyProperties']);
        $this->assertEmpty($result['requiredBodyProperties']);
    }

    public function test_convertParameters_jsonMethod_paramsGoToBodyProperties() : void
    {
        $param = $this->createParamMock('bodyField', 'String', true);

        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array($param));

        $converter = new ParameterConverter($this->createJsonMethodMock());
        $result = $converter->convertParameters($manager);

        $this->assertEmpty($result['parameters']);
        $this->assertArrayHasKey('bodyField', $result['requestBodyProperties']);
        $this->assertContains('bodyField', $result['requiredBodyProperties']);
    }

    public function test_convertParameters_reservedParamsAreExcluded() : void
    {
        $methodParam = $this->createParamMock('method');
        $apiVersionParam = $this->createParamMock('apiVersion');
        $realParam = $this->createParamMock('realParam');

        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array($methodParam, $apiVersionParam, $realParam));

        $converter = new ParameterConverter($this->createQueryMethodMock());
        $result = $converter->convertParameters($manager);

        $this->assertCount(1, $result['parameters']);
        $this->assertSame('query', $result['parameters'][0]['in']);
        $this->assertSame('realParam', $result['parameters'][0]['name']);
    }
}
