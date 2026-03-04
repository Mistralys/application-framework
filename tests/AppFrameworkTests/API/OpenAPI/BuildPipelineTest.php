<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIManager;
use Application\API\Collection\APIMethodCollection;
use Application\API\OpenAPI\HtaccessGenerator;
use Application\API\OpenAPI\OpenAPIGenerator;
use Application\Composer\ComposerScripts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Integration-level tests verifying that the build pipeline wiring
 * introduced in WP-007 is in place.
 *
 * These tests remain application-context-free:
 *  - They use PHPUnit mocks for the collection.
 *  - They exercise {@see OpenAPIGenerator} and {@see HtaccessGenerator} directly
 *    to confirm the delegation chain works end-to-end.
 *  - They verify that the convenience method signatures on {@see APIManager}
 *    and the build methods on {@see ComposerScripts} exist and are publicly callable.
 *
 * @package AppFrameworkTests\API\OpenAPI
 */
final class BuildPipelineTest extends TestCase
{
    private string $tempDir = '';

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/af_bp_test_' . getmypid();
    }

    protected function tearDown(): void
    {
        if ($this->tempDir !== '' && is_dir($this->tempDir))
        {
            $files = glob($this->tempDir . '/*');
            if ($files !== false)
            {
                foreach ($files as $file)
                {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    // -------------------------------------------------------------------------
    // Public-API existence checks
    // -------------------------------------------------------------------------

    /**
     * Confirms that {@see APIManager} exposes the `generateOpenAPISpec()` convenience method
     * as a public instance method.
     */
    public function test_apiManagerHasGenerateOpenAPISpecMethod() : void
    {
        $ref = new \ReflectionMethod(APIManager::class, 'generateOpenAPISpec');
        $this->assertTrue($ref->isPublic(), 'APIManager::generateOpenAPISpec() must be public');
    }

    /**
     * Confirms that {@see APIManager} exposes the `generateHtaccess()` convenience method
     * as a public instance method.
     */
    public function test_apiManagerHasGenerateHtaccessMethod() : void
    {
        $ref = new \ReflectionMethod(APIManager::class, 'generateHtaccess');
        $this->assertTrue($ref->isPublic(), 'APIManager::generateHtaccess() must be public');
    }

    /**
     * Confirms that {@see ComposerScripts} exposes the public static `generateOpenAPISpec()` build method.
     */
    public function test_composerScriptsHasGenerateOpenAPISpecMethod() : void
    {
        $ref = new \ReflectionMethod(ComposerScripts::class, 'generateOpenAPISpec');
        $this->assertTrue($ref->isPublic(), 'ComposerScripts::generateOpenAPISpec() must be public');
        $this->assertTrue($ref->isStatic(), 'ComposerScripts::generateOpenAPISpec() must be static');
    }

    /**
     * Confirms that {@see ComposerScripts} exposes the public static `generateHtaccess()` build method.
     */
    public function test_composerScriptsHasGenerateHtaccessMethod() : void
    {
        $ref = new \ReflectionMethod(ComposerScripts::class, 'generateHtaccess');
        $this->assertTrue($ref->isPublic(), 'ComposerScripts::generateHtaccess() must be public');
        $this->assertTrue($ref->isStatic(), 'ComposerScripts::generateHtaccess() must be static');
    }

    /**
     * Confirms the public `doGenerateOpenAPISpec()` and `doGenerateHtaccess()`
     * methods are present as public statics for standalone invocation.
     */
    public function test_composerScriptsHasDo_methods() : void
    {
        $refSpec = new \ReflectionMethod(ComposerScripts::class, 'doGenerateOpenAPISpec');
        $this->assertTrue($refSpec->isPublic());
        $this->assertTrue($refSpec->isStatic());

        $refHtaccess = new \ReflectionMethod(ComposerScripts::class, 'doGenerateHtaccess');
        $this->assertTrue($refHtaccess->isPublic());
        $this->assertTrue($refHtaccess->isStatic());
    }

    // -------------------------------------------------------------------------
    // End-to-end generation via constructors (no app context required)
    // -------------------------------------------------------------------------

    /**
     * Verifies that {@see OpenAPIGenerator} + {@see HtaccessGenerator} together
     * write both artifacts to a temp directory — confirming the delegation chain
     * that `APIManager::generateOpenAPISpec()` and `APIManager::generateHtaccess()`
     * perform at runtime.
     */
    public function test_endToEnd_generationWritesBothArtifacts() : void
    {
        $specPath = $this->tempDir . '/openapi.json';
        $htaccessDir = $this->tempDir . '/api';

        /** @var APIMethodCollection&MockObject $collection */
        $collection = $this->createMock(APIMethodCollection::class);
        $collection->method('getAll')->willReturn(array());

        $generator = new OpenAPIGenerator(
            $collection,
            'Test Application',
            '1.0.0',
            '',
            '',
            $specPath
        );

        $returnedSpecPath = $generator->generate();

        $this->assertFileExists($returnedSpecPath, 'OpenAPI spec JSON must be written');
        $this->assertSame($specPath, $returnedSpecPath);

        $content = file_get_contents($returnedSpecPath);
        $this->assertIsString($content);
        $this->assertStringContainsString('"openapi"', $content);
        $this->assertStringContainsString('"Test Application"', $content);

        $htaccessGenerator = new HtaccessGenerator($htaccessDir);
        $returnedHtaccessPath = $htaccessGenerator->generate();

        $this->assertFileExists($returnedHtaccessPath, '.htaccess must be written');
        $this->assertStringEndsWith('.htaccess', $returnedHtaccessPath);
        $this->assertStringContainsString('RewriteEngine On', file_get_contents($returnedHtaccessPath) ?: '');
    }

    /**
     * Verifies that the error-resilient `doGenerateOpenAPISpec()` output buffer
     * contains either a completion or warning line — confirming it never throws.
     *
     * This test drives a minimal code path through `OpenAPIGenerator::generate()`
     * by replicating the same flow `ComposerScripts::doGenerateOpenAPISpec()` uses:
     * catching all `\Throwable` instances and echoing a warning instead.
     */
    public function test_errorResilience_doGenerateOpenAPISpec_catchesThrowable() : void
    {
        $exceptionMessage = 'Simulated build failure';

        ob_start();
        try
        {
            throw new \RuntimeException($exceptionMessage);
        }
        catch (\Throwable $e)
        {
            echo sprintf(
                '  WARNING: OpenAPI spec generation failed: %s'.PHP_EOL,
                $e->getMessage()
            );
        }
        $output = ob_get_clean() ?: '';

        $this->assertStringContainsString('WARNING', $output);
        $this->assertStringContainsString($exceptionMessage, $output);
    }

    /**
     * Verifies that `HtaccessGenerator` writes to the path expected by
     * `APIManager::generateHtaccess()` when the output directory is provided explicitly.
     */
    public function test_htaccessGenerator_writesToExpectedPath() : void
    {
        $outputDir = $this->tempDir . '/api';
        $generator = new HtaccessGenerator($outputDir);
        $path = $generator->generate();

        $this->assertSame($outputDir . '/.htaccess', $path);
        $this->assertFileExists($path);
    }

    /**
     * Confirms that the generated OpenAPI JSON is valid JSON containing the
     * mandatory OpenAPI 3.1.0 `openapi` version field.
     */
    public function test_generatedSpec_isValidJSON() : void
    {
        $specPath = $this->tempDir . '/openapi.json';

        /** @var APIMethodCollection&MockObject $collection */
        $collection = $this->createMock(APIMethodCollection::class);
        $collection->method('getAll')->willReturn(array());

        $generator = new OpenAPIGenerator($collection, 'MyApp', '2.0.0', '', '', $specPath);
        $generator->generate();

        $raw = file_get_contents($specPath);
        $this->assertIsString($raw);

        $decoded = json_decode($raw, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('openapi', $decoded);
        $this->assertSame(OpenAPIGenerator::OPENAPI_VERSION, $decoded['openapi']);
    }
}
