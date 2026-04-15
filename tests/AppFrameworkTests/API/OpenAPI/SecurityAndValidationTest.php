<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Collection\APIMethodCollection;
use Application\API\Groups\APIGroupInterface;
use Application\API\OpenAPI\MethodConverter;
use Application\API\OpenAPI\OpenAPIGenerator;
use Application\API\OpenAPI\OpenAPISchema;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\Rules\RuleInterface;
use Application\API\Parameters\Rules\Type\OrRule;
use Application\API\Parameters\Rules\Type\RequiredIfOtherIsSetRule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UI\AdminURLs\AdminURLInterface;

/**
 * Unit tests verifying the security scheme and validation-rule documentation
 * features introduced by WP-010.
 *
 * - {@see OpenAPISchema::getSecuritySchemes()} and the `SECURITY_SCHEME_API_KEY` constant.
 * - {@see OpenAPIGenerator} includes `securitySchemes` in the `components` section.
 * - {@see MethodConverter} adds a `security` requirement for {@see APIKeyMethodInterface} methods.
 * - {@see MethodConverter} adds `x-validation-rules` when a method has parameter rules.
 *
 * @package AppFrameworkTests\API\OpenAPI
 */
final class SecurityAndValidationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a mock `APIParamManager` with no parameters and the given rules.
     *
     * @param RuleInterface[] $rules
     * @return APIParamManager&MockObject
     */
    private function createParamManagerMock(array $rules = array()) : APIParamManager
    {
        $manager = $this->createMock(APIParamManager::class);
        $manager->method('getParams')->willReturn(array());
        $manager->method('getRules')->willReturn($rules);
        return $manager;
    }

    /**
     * Creates a mock `AdminURLInterface` whose `__toString()` returns an empty string.
     *
     * @return AdminURLInterface&MockObject
     */
    private function createAdminUrlMock() : AdminURLInterface
    {
        $url = $this->createMock(AdminURLInterface::class);
        $url->method('__toString')->willReturn('');
        return $url;
    }

    /**
     * Creates a mock `APIGroupInterface` with default labels.
     *
     * @return APIGroupInterface&MockObject
     */
    private function createGroupMock() : APIGroupInterface
    {
        $group = $this->createMock(APIGroupInterface::class);
        $group->method('getLabel')->willReturn('Test Group');
        $group->method('getDescription')->willReturn('');
        return $group;
    }

    /**
     * Configures the common stubs shared by all method mocks.
     *
     * @param APIMethodInterface&MockObject $method
     * @param RuleInterface[] $rules
     */
    private function configureMethodStubs(APIMethodInterface $method, array $rules = array()) : void
    {
        $method->method('getMethodName')->willReturn('TestMethod');
        $method->method('getDescription')->willReturn('Test description.');
        $method->method('getGroup')->willReturn($this->createGroupMock());
        $method->method('getDocumentationURL')->willReturn($this->createAdminUrlMock());
        $method->method('getVersions')->willReturn(array('1.0'));
        $method->method('getCurrentVersion')->willReturn('1.0');
        $method->method('getChangelog')->willReturn(array());
        $method->method('getRelatedMethodNames')->willReturn(array());
        $method->method('getResponseMime')->willReturn('application/json');
        $method->method('manageParams')->willReturn($this->createParamManagerMock($rules));
    }

    /**
     * Creates a regular `APIMethodInterface` mock — no API key requirement.
     *
     * @param RuleInterface[] $rules
     * @return APIMethodInterface&MockObject
     */
    private function createPlainMethodMock(array $rules = array()) : APIMethodInterface
    {
        /** @var APIMethodInterface&MockObject $method */
        $method = $this->createMock(APIMethodInterface::class);
        $this->configureMethodStubs($method, $rules);
        return $method;
    }

    /**
     * Creates an `APIKeyMethodInterface` mock — passes `instanceof APIKeyMethodInterface`
     * and `instanceof APIMethodInterface` (because the interface extends the other).
     *
     * @param RuleInterface[] $rules
     * @return APIKeyMethodInterface&MockObject
     */
    private function createApiKeyMethodMock(array $rules = array()) : APIKeyMethodInterface
    {
        /** @var APIKeyMethodInterface&MockObject $method */
        $method = $this->createMock(APIKeyMethodInterface::class);
        $this->configureMethodStubs($method, $rules);
        return $method;
    }

    /**
     * Creates a minimal `APIParameterInterface` mock returning the given name.
     *
     * @param string $name
     * @return APIParameterInterface&MockObject
     */
    private function createParamMock(string $name) : APIParameterInterface
    {
        /** @var APIParameterInterface&MockObject $param */
        $param = $this->createMock(APIParameterInterface::class);
        $param->method('getName')->willReturn($name);
        return $param;
    }

    /**
     * Creates an `OrRule` mock that exposes the given parameter names via `getParams()`.
     *
     * @param string[] $paramNames
     * @return OrRule&MockObject
     */
    private function createOrRuleMock(array $paramNames) : OrRule
    {
        $params = array_map(array($this, 'createParamMock'), $paramNames);

        /** @var OrRule&MockObject $rule */
        $rule = $this->createMock(OrRule::class);
        $rule->method('getID')->willReturn(OrRule::RULE_ID);
        $rule->method('getLabel')->willReturn('At least one set');
        $rule->method('getTypeDescription')->willReturn('');
        $rule->method('getParams')->willReturn($params);
        return $rule;
    }

    /**
     * Creates a `RequiredIfOtherIsSetRule` mock for the given target and trigger param names.
     *
     * @param string $targetName
     * @param string $otherName
     * @return RequiredIfOtherIsSetRule&MockObject
     */
    private function createRequiredIfRuleMock(string $targetName, string $otherName) : RequiredIfOtherIsSetRule
    {
        /** @var RequiredIfOtherIsSetRule&MockObject $rule */
        $rule = $this->createMock(RequiredIfOtherIsSetRule::class);
        $rule->method('getID')->willReturn(RequiredIfOtherIsSetRule::RULE_ID);
        $rule->method('getLabel')->willReturn('Required if other is set');
        $rule->method('getTypeDescription')->willReturn('');
        $rule->method('getParams')->willReturn(array(
            $this->createParamMock($targetName),
            $this->createParamMock($otherName),
        ));
        return $rule;
    }

    /**
     * Converts a method via `MethodConverter` and returns the single operation array.
     *
     * Avoids the PHPStan `argument.byRef` violation that arises from passing
     * `reset()` return values directly to another `reset()` call.
     *
     * @param APIMethodInterface $method
     * @return array<string,mixed>
     */
    private function extractOperation(APIMethodInterface $method) : array
    {
        $result    = (new MethodConverter())->convertMethod($method);
        $pathItem  = reset($result);
        $operation = reset($pathItem);
        return (array)$operation;
    }

    /**
     * Creates an `APIMethodCollection` mock returning the given methods.
     *
     * @param APIMethodInterface[] $methods
     * @return APIMethodCollection&MockObject
     */
    private function createCollectionMock(array $methods = array()) : APIMethodCollection
    {
        /** @var APIMethodCollection&MockObject $col */
        $col = $this->createMock(APIMethodCollection::class);
        $col->method('getAll')->willReturn($methods);
        return $col;
    }

    // -------------------------------------------------------------------------
    // OpenAPISchema — security scheme definition
    // -------------------------------------------------------------------------

    /**
     * The `SECURITY_SCHEME_API_KEY` constant must equal `'apiKey'`.
     */
    public function test_securitySchemeConstant_isApiKey() : void
    {
        $this->assertSame('apiKey', OpenAPISchema::SECURITY_SCHEME_API_KEY);
    }

    /**
     * `getSecuritySchemes()` must return an entry keyed by `apiKey` with the expected shape.
     */
    public function test_getSecuritySchemes_returnsHttpBearerScheme() : void
    {
        $schema = new OpenAPISchema();
        $schemes = $schema->getSecuritySchemes();

        $this->assertArrayHasKey(OpenAPISchema::SECURITY_SCHEME_API_KEY, $schemes);

        $scheme = $schemes[OpenAPISchema::SECURITY_SCHEME_API_KEY];
        $this->assertSame('http', $scheme['type']);
        $this->assertSame('bearer', $scheme['scheme']);
        $this->assertArrayHasKey('description', $scheme);
        $this->assertNotEmpty($scheme['description']);
    }

    // -------------------------------------------------------------------------
    // OpenAPIGenerator — components/securitySchemes
    // -------------------------------------------------------------------------

    /**
     * The generated spec's `components` must include `securitySchemes`.
     */
    public function test_generator_specIncludesSecuritySchemesInComponents() : void
    {
        $collection = $this->createCollectionMock();
        $spec = (new OpenAPIGenerator($collection, 'App', '1.0'))->toArray();

        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey(
            OpenAPISchema::SECURITY_SCHEME_API_KEY,
            $spec['components']['securitySchemes']
        );
    }

    /**
     * The existing `schemas` section must still be present alongside the new `securitySchemes`.
     */
    public function test_generator_specStillContainsSchemasSection() : void
    {
        $collection = $this->createCollectionMock();
        $spec = (new OpenAPIGenerator($collection, 'App', '1.0'))->toArray();

        $this->assertArrayHasKey('schemas', $spec['components']);
        $this->assertArrayHasKey(OpenAPISchema::SCHEMA_API_ENVELOPE, $spec['components']['schemas']);
    }

    // -------------------------------------------------------------------------
    // MethodConverter — per-method security
    // -------------------------------------------------------------------------

    /**
     * A plain method (no `APIKeyMethodInterface`) must NOT carry a `security` field.
     */
    public function test_methodConverter_unauthenticatedMethodHasNoSecurityField() : void
    {
        $operation = $this->extractOperation($this->createPlainMethodMock());

        $this->assertArrayNotHasKey('security', $operation);
    }

    /**
     * A method implementing `APIKeyMethodInterface` MUST carry a `security` requirement
     * referencing the `apiKey` scheme with an empty scope array.
     */
    public function test_methodConverter_apiKeyMethodHasSecurityRequirement() : void
    {
        $operation = $this->extractOperation($this->createApiKeyMethodMock());

        $this->assertArrayHasKey('security', $operation);

        $security = $operation['security'];
        $this->assertIsArray($security);
        $this->assertNotEmpty($security);

        $firstEntry = $security[0];
        $this->assertArrayHasKey(OpenAPISchema::SECURITY_SCHEME_API_KEY, $firstEntry);
        $this->assertSame(array(), $firstEntry[OpenAPISchema::SECURITY_SCHEME_API_KEY]);
    }

    // -------------------------------------------------------------------------
    // MethodConverter — x-validation-rules
    // -------------------------------------------------------------------------

    /**
     * A method with no validation rules must NOT have an `x-validation-rules` field.
     */
    public function test_methodConverter_noRulesOmitsXValidationRules() : void
    {
        $operation = $this->extractOperation($this->createPlainMethodMock());

        $this->assertArrayNotHasKey('x-validation-rules', $operation);
    }

    /**
     * A method with an `OrRule` must produce an `x-validation-rules` entry with:
     * - `id` = `OR`
     * - `description` containing all involved parameter names
     * - `parameters` array containing all involved parameter names
     */
    public function test_methodConverter_orRuleProducesReadableConstraint() : void
    {
        $rule      = $this->createOrRuleMock(array('fieldA', 'fieldB'));
        $method    = $this->createPlainMethodMock(array($rule));
        $operation = $this->extractOperation($method);

        $this->assertArrayHasKey('x-validation-rules', $operation);

        $entry = $operation['x-validation-rules'][0];
        $this->assertSame(OrRule::RULE_ID, $entry['id']);
        $this->assertStringContainsString('fieldA', $entry['description']);
        $this->assertStringContainsString('fieldB', $entry['description']);
        $this->assertContains('fieldA', $entry['parameters']);
        $this->assertContains('fieldB', $entry['parameters']);
    }

    /**
     * A method with a `RequiredIfOtherIsSetRule` must produce an `x-validation-rules` entry
     * mentioning both the target and trigger parameter names.
     */
    public function test_methodConverter_requiredIfOtherIsSetProducesReadableConstraint() : void
    {
        $rule      = $this->createRequiredIfRuleMock('targetParam', 'triggerParam');
        $method    = $this->createPlainMethodMock(array($rule));
        $operation = $this->extractOperation($method);

        $this->assertArrayHasKey('x-validation-rules', $operation);

        $entry = $operation['x-validation-rules'][0];
        $this->assertSame(RequiredIfOtherIsSetRule::RULE_ID, $entry['id']);
        $this->assertStringContainsString('targetParam', $entry['description']);
        $this->assertStringContainsString('triggerParam', $entry['description']);
    }

    /**
     * A method with multiple rules produces one `x-validation-rules` entry per rule, in order.
     */
    public function test_methodConverter_multipleRulesAllAppear() : void
    {
        $orRule       = $this->createOrRuleMock(array('x', 'y'));
        $requiredRule = $this->createRequiredIfRuleMock('target', 'other');
        $method       = $this->createPlainMethodMock(array($orRule, $requiredRule));
        $operation    = $this->extractOperation($method);

        $this->assertArrayHasKey('x-validation-rules', $operation);
        $this->assertCount(2, $operation['x-validation-rules']);

        $ids = array_column($operation['x-validation-rules'], 'id');
        $this->assertContains(OrRule::RULE_ID, $ids);
        $this->assertContains(RequiredIfOtherIsSetRule::RULE_ID, $ids);
    }

    /**
     * An unknown rule type uses `getTypeDescription()` as the description.
     */
    public function test_methodConverter_unknownRuleFallsBackToTypeDescription() : void
    {
        $param = $this->createParamMock('someParam');

        /** @var RuleInterface&MockObject $rule */
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getID')->willReturn('CUSTOM_RULE');
        $rule->method('getLabel')->willReturn('My custom rule');
        $rule->method('getTypeDescription')->willReturn('A custom constraint applies here.');
        $rule->method('getParams')->willReturn(array($param));

        $operation = $this->extractOperation($this->createPlainMethodMock(array($rule)));
        $entry     = $operation['x-validation-rules'][0];

        $this->assertSame('A custom constraint applies here.', $entry['description']);
    }

    /**
     * When `getTypeDescription()` returns an empty string, the rule `getLabel()` is used.
     */
    public function test_methodConverter_ruleWithEmptyTypeDescriptionUsesLabel() : void
    {
        $param = $this->createParamMock('param1');

        /** @var RuleInterface&MockObject $rule */
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('getID')->willReturn('SOME_RULE');
        $rule->method('getLabel')->willReturn('Fallback label text');
        $rule->method('getTypeDescription')->willReturn('');
        $rule->method('getParams')->willReturn(array($param));

        $operation = $this->extractOperation($this->createPlainMethodMock(array($rule)));
        $entry     = $operation['x-validation-rules'][0];

        $this->assertSame('Fallback label text', $entry['description']);
    }
}
