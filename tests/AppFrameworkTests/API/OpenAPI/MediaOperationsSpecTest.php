<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIManager;
use Application\API\OpenAPI\OpenAPIGenerator;
use Application\Media\API\Methods\AddMediaDocumentAPI;
use Application\Media\API\Methods\DeleteMediaDocumentAPI;
use Application\Media\API\Methods\GetMediaDocumentAPI;
use Application\Media\API\Methods\GetMediaDocumentsAPI;
use Application\Media\API\Methods\UpdateMediaDocumentAPI;
use Application\Media\API\MediaFileUploadAPIInterface;
use AppFrameworkTestClasses\ApplicationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Covers AC-06: the generated OpenAPI spec lists all five media operations
 * with their security requirement, and the two JSON-body write methods have
 * a `requestBody` with `fileContent` mapped as `contentEncoding: base64`.
 *
 * Builds the spec in memory through {@see OpenAPIGenerator::toArray()} (no
 * filesystem I/O), against the real, framework-registered method collection
 * — the same collection the `composer generate-openapi-spec` script writes
 * to `api/openapi.json`.
 */
final class MediaOperationsSpecTest extends ApplicationTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function generateSpec() : array
    {
        $generator = new OpenAPIGenerator(
            APIManager::getInstance()->getMethodCollection(),
            'Test Application',
            '1.0.0'
        );

        return $generator->toArray();
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function mediaMethodNameProvider() : array
    {
        return array(
            array(AddMediaDocumentAPI::METHOD_NAME),
            array(GetMediaDocumentAPI::METHOD_NAME),
            array(GetMediaDocumentsAPI::METHOD_NAME),
            array(UpdateMediaDocumentAPI::METHOD_NAME),
            array(DeleteMediaDocumentAPI::METHOD_NAME),
        );
    }

    #[DataProvider('mediaMethodNameProvider')]
    public function test_allFiveMediaOperationsArePresentWithSecurity(string $methodName) : void
    {
        $spec = $this->generateSpec();

        $path = '/api/'.$methodName;

        $this->assertArrayHasKey($path, $spec['paths'], sprintf('Path [%s] must be present in the generated spec.', $path));

        $operation = $spec['paths'][$path]['post'];

        $this->assertArrayHasKey('security', $operation, sprintf('Operation [%s] must declare a security requirement.', $methodName));
        $this->assertNotEmpty($operation['security']);
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function jsonBodyMethodNameProvider() : array
    {
        return array(
            array(AddMediaDocumentAPI::METHOD_NAME),
            array(UpdateMediaDocumentAPI::METHOD_NAME),
        );
    }

    #[DataProvider('jsonBodyMethodNameProvider')]
    public function test_jsonBodyMethodsHaveFileContentAsBase64RequestBody(string $methodName) : void
    {
        $spec = $this->generateSpec();

        $operation = $spec['paths']['/api/'.$methodName]['post'];

        $this->assertArrayHasKey('requestBody', $operation, sprintf('Operation [%s] must declare a requestBody.', $methodName));

        $schema = $operation['requestBody']['content']['application/json']['schema'];
        $fileContentSchema = $schema['properties'][MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] ?? null;

        $this->assertIsArray($fileContentSchema, sprintf('Operation [%s] must declare a fileContent body property.', $methodName));
        $this->assertSame('string', $fileContentSchema['type']);
        $this->assertSame('base64', $fileContentSchema['contentEncoding']);
    }

    /**
     * The read methods (RequestRequestInterface, not JSONRequestInterface)
     * carry their parameters as query parameters, never a requestBody.
     */
    public function test_readMethodsUseQueryParametersNotRequestBody() : void
    {
        $spec = $this->generateSpec();

        foreach(array(GetMediaDocumentAPI::METHOD_NAME, GetMediaDocumentsAPI::METHOD_NAME) as $methodName) {
            $operation = $spec['paths']['/api/'.$methodName]['post'];

            $this->assertArrayNotHasKey('requestBody', $operation, sprintf('[%s] must not declare a requestBody.', $methodName));
            $this->assertNotEmpty($operation['parameters'], sprintf('[%s] must declare query parameters.', $methodName));
        }
    }
}
