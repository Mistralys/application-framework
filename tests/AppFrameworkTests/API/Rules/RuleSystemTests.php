<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Rules;

use AppFrameworkTestClasses\API\StubAPIMethod;
use Application\API\APIException;
use Application\API\APIManager;
use Application\API\Parameters\ParamSet;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class RuleSystemTests extends APITestCase
{
    /**
     * Once a method's parameters have been validated, no further rules
     * may be added.
     */
    public function test_cannotAddRuleAfterValidation() : void
    {
        $method = new StubAPIMethod(APIManager::getInstance());

        $manager = $method->manageParams();

        $manager->addRule()
            ->or('A rule')
            ->addSet(new ParamSet('a', new StringParameter('paramA', 'Param A')));

        $manager->getValidationResults();

        $this->expectException(APIException::class);
        $this->expectExceptionCode(APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION);

        $manager->addRule()
            ->or('Another rule')
            ->addSet(new ParamSet('b', new StringParameter('paramB', 'Param B')));
    }

    public function test_cannotRegisterParamsAfterValidation() : void
    {
        $method = new StubAPIMethod(APIManager::getInstance());

        $manager = $method->manageParams();

        $manager->getValidationResults();

        $this->expectException(APIException::class);
        $this->expectExceptionCode(APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION);

        $manager->registerParam(new StringParameter('paramA', 'Param A'));
    }
}
