<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Collection\APIMethodCollection;
use Application\API\Groups\APIGroupInterface;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper\FileInfo;
use Throwable;

/**
 * Main orchestrator for OpenAPI 3.1 specification generation.
 *
 * Iterates all API methods, delegates per-method conversion to {@see MethodConverter},
 * assembles the top-level document structure (info, servers, paths, tags, components)
 * and serialises the result as a pretty-printed JSON file.
 *
 * ## Production usage
 *
 * Use the named constructor {@see createFromDefaults()} when working inside the live
 * application context. It pulls the method collection, app name, version, and server
 * URL from the running framework.
 *
 * ## Testing usage
 *
 * Construct directly, passing a mocked `APIMethodCollection` and the scalar metadata
 * values you want to verify.
 *
 * ## Error resilience
 *
 * Individual method conversion failures (e.g. methods that require DB state) are
 * caught, logged via `error_log()`, and skipped. Generation never aborts.
 *
 * @package API
 * @subpackage OpenAPI
 */
class OpenAPIGenerator
{
    public const string OPENAPI_VERSION = '3.1.0';
    public const string DEFAULT_OUTPUT_RELATIVE = 'storage/api/openapi.json';

    private APIMethodCollection $methodCollection;
    private string $appName;
    private string $appVersion;
    private string $description;
    private string $serverUrl;
    private string $outputPath;
    private MethodConverter $methodConverter;
    private OpenAPISchema $schema;

    /**
     * @var array<string, string> Indexed by method name, value is the exception message.
     */
    private array $conversionErrors = array();

    /**
     * @param APIMethodCollection $methodCollection  Collection to iterate.
     * @param string $appName                        Application name for the `info.title` field.
     * @param string $appVersion                     Application version for the `info.version` field.
     * @param string $description                    Optional `info.description`.
     * @param string $serverUrl                      Server URL for the `servers` entry. Default empty.
     * @param string $outputPath                     Absolute path the JSON file is written to.
     *                                               Default: `APP_INSTALL_FOLDER/storage/api/openapi.json`.
     */
    public function __construct(
        APIMethodCollection $methodCollection,
        string $appName,
        string $appVersion,
        string $description = '',
        string $serverUrl = '',
        string $outputPath = ''
    )
    {
        $this->methodCollection = $methodCollection;
        $this->appName = $appName;
        $this->appVersion = $appVersion;
        $this->description = $description;
        $this->serverUrl = $serverUrl;
        $this->outputPath = $outputPath !== ''
            ? $outputPath
            : (defined('APP_INSTALL_FOLDER') ? APP_INSTALL_FOLDER.'/'.self::DEFAULT_OUTPUT_RELATIVE : '');
        $this->methodConverter = new MethodConverter();
        $this->schema = new OpenAPISchema();
    }

    // -------------------------------------------------------------------------
    // Configuration (fluent setters)
    // -------------------------------------------------------------------------

    /**
     * Sets a custom absolute path for the generated JSON file.
     *
     * @param string $path
     * @return $this
     */
    public function setOutputPath(string $path) : self
    {
        $this->outputPath = $path;
        return $this;
    }

    /**
     * Sets the server URL to include in the `servers` section.
     *
     * @param string $url
     * @return $this
     */
    public function setServerUrl(string $url) : self
    {
        $this->serverUrl = $url;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Public generation API
    // -------------------------------------------------------------------------

    /**
     * Generates the OpenAPI 3.1 specification document and writes it as a
     * pretty-printed JSON file to the configured output path.
     *
     * @return string The absolute path of the written file.
     * @throws JSONConverterException If JSON serialisation fails.
     */
    public function generate() : string
    {
        $json = JSONConverter::var2json(
            $this->toArray(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        FileInfo::factory($this->outputPath)->putContents($json);

        return $this->outputPath;
    }

    /**
     * Assembles and returns the complete OpenAPI 3.1 specification as a PHP array.
     *
     * This is the primary entry point for testing — no filesystem I/O is performed.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        $this->conversionErrors = array();

        $paths = array();
        $groups = array();

        foreach($this->methodCollection->getAll() as $method)
        {
            $this->processMethod($method, $paths, $groups);
        }

        $spec = array(
            'openapi' => self::OPENAPI_VERSION,
            'info' => $this->buildInfo(),
            'servers' => $this->buildServers(),
            'tags' => $this->buildTags($groups),
            'paths' => $paths,
            'components' => array(
                'schemas' => $this->schema->getComponentSchemas(),
                'securitySchemes' => $this->schema->getSecuritySchemes(),
            ),
        );

        return $spec;
    }

    /**
     * Returns an array of method names whose conversion failed during the last {@see toArray()} call.
     *
     * @return array<string, string> Method name → exception message.
     */
    public function getConversionErrors() : array
    {
        return $this->conversionErrors;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Converts a single method and merges the result into `$paths` and `$groups`.
     *
     * Failures are caught, logged, and recorded in {@see $conversionErrors} rather
     * than aborting the whole generation run.
     *
     * @param APIMethodInterface $method
     * @param array<string, mixed> $paths Output paths array (modified in place).
     * @param array<string, APIGroupInterface> $groups Unique groups map (modified in place).
     */
    private function processMethod(APIMethodInterface $method, array &$paths, array &$groups) : void
    {
        try
        {
            $pathItem = $this->methodConverter->convertMethod($method);
            $paths = array_merge($paths, $pathItem);

            $group = $method->getGroup();
            $groupId = $group->getLabel();
            if(!isset($groups[$groupId]))
            {
                $groups[$groupId] = $group;
            }
        }
        catch(Throwable $e)
        {
            $methodName = $method->getMethodName();
            $message = sprintf('[OpenAPIGenerator] Skipping method "%s": %s', $methodName, $e->getMessage());
            error_log($message);
            $this->conversionErrors[$methodName] = $e->getMessage();
        }
    }

    /**
     * Builds the OpenAPI `info` object.
     *
     * @return array<string, string>
     */
    private function buildInfo() : array
    {
        $info = array(
            'title' => $this->appName,
            'version' => $this->appVersion,
        );

        if($this->description !== '')
        {
            $info['description'] = $this->description;
        }

        return $info;
    }

    /**
     * Builds the OpenAPI `servers` array.
     *
     * @return array<int, array<string, string>>
     */
    private function buildServers() : array
    {
        if($this->serverUrl === '')
        {
            return array();
        }

        return array(
            array('url' => $this->serverUrl),
        );
    }

    /**
     * Builds the OpenAPI `tags` array from the collected unique API groups.
     *
     * @param array<string, APIGroupInterface> $groups Map of unique groups (key = group label).
     * @return array<int, array<string, string>>
     */
    private function buildTags(array $groups) : array
    {
        $tags = array();

        foreach($groups as $group)
        {
            $tags[] = array(
                'name' => $group->getLabel(),
                'description' => $group->getDescription(),
            );
        }

        return $tags;
    }
}
