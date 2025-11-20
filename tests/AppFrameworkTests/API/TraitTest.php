<?php

declare(strict_types=1);

namespace AppFrameworkTests\API;

use Application\API\APIManager;
use Application\API\Traits\DryRunAPIInterface;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;
use TestDriver\API\TestDryRunMethod;

final class TraitTest extends APITestCase
{
    public function test_dryRunIsDisabledByDefault() : void
    {
        $method = new TestDryRunMethod(APIManager::getInstance());

        $this->assertFalse(
            $method->isDryRun(),
            'Dry run should be disabled by default.'
        );
    }

    public function test_dryRunCanBeEnabledManually() : void
    {
        $method = new TestDryRunMethod(APIManager::getInstance());
        $method->selectDryRun(true);

        $this->assertTrue(
            $method->isDryRun(),
            'Dry run should be enabled after selecting it.'
        );
    }

    public function test_dryRunCanBeEnabledViaRequest() : void
    {
        $_REQUEST[DryRunAPIInterface::PARAM_DRY_RUN] = 'true';

        $method = new TestDryRunMethod(APIManager::getInstance());

        $response = $this->assertSuccessfulResponse($method);

        $this->assertTrue($method->isDryRun());

        $this->assertTrue(
            $response->getBool(DryRunAPIInterface::PARAM_DRY_RUN),
            'Dry run should be enabled when the request parameter is set to true.'
        );

    }

    public function test_dryRunParamRegistration() : void
    {
        $method = new TestDryRunMethod(APIManager::getInstance());

        $this->assertNotNull(
            $method->getDryRunParam(),
            'Dry run parameter should be registered and not null.'
        );
    }
}
