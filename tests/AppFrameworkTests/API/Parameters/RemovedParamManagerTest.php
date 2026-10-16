<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Parameters;

use AppFrameworkTestClasses\API\StubAPIMethod;
use Application\API\APIException;
use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterException;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\Removed\RemovedParamCollection;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class RemovedParamManagerTest extends APITestCase
{
    private function createManager() : APIParamManager
    {
        $method = new StubAPIMethod(APIManager::getInstance());

        return $method->manageParams();
    }

    // region: Declaration and readback

    public function test_addRemovedParamIsReadableBack() : void
    {
        $manager = $this->createManager();

        $param = $manager->addRemovedParam('legacy_flag');
        $param->setReplacement('new_flag')
            ->setRemovedSince('2.5.0')
            ->setNote('See the migration guide.');

        $removed = $manager->getRemovedParams();

        $this->assertTrue($removed->has('legacy_flag'));
        $this->assertSame($param, $removed->getAll()[0]);
        $this->assertSame('new_flag', $param->getReplacement());
        $this->assertSame('2.5.0', $param->getRemovedSince());
        $this->assertSame('See the migration guide.', $param->getNote());
    }

    public function test_getRemovedParamsIsEmptyByDefault() : void
    {
        $manager = $this->createManager();

        $this->assertTrue($manager->getRemovedParams()->isEmpty());
    }

    // endregion

    // region: Conflict detection

    public function test_addRemovedParamAfterRegisterParamThrows() : void
    {
        $manager = $this->createManager();
        $manager->registerParam(new StringParameter('shared_name', 'Shared Param'));

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_REMOVED_PARAM_IS_REGISTERED);

        $manager->addRemovedParam('shared_name');
    }

    public function test_registerParamAfterAddRemovedParamThrows() : void
    {
        $manager = $this->createManager();
        $manager->addRemovedParam('shared_name');

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_REMOVED_PARAM_IS_REGISTERED);

        $manager->registerParam(new StringParameter('shared_name', 'Shared Param'));
    }

    public function test_addRemovedParamWithReservedNameThrows() : void
    {
        $manager = $this->createManager();

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_REMOVED_PARAM_RESERVED_NAME);

        $manager->addRemovedParam(APIMethodInterface::REQUEST_PARAM_METHOD);
    }

    public function test_addRemovedParamWithReservedVersionNameThrows() : void
    {
        $manager = $this->createManager();

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_REMOVED_PARAM_RESERVED_NAME);

        $manager->addRemovedParam(APIMethodInterface::REQUEST_PARAM_API_VERSION);
    }

    public function test_addRemovedParamTwiceThrows() : void
    {
        $manager = $this->createManager();
        $manager->addRemovedParam('legacy_flag');

        $this->expectException(APIParameterException::class);
        $this->expectExceptionCode(APIParameterException::ERROR_REMOVED_PARAM_ALREADY_DECLARED);

        $manager->addRemovedParam('legacy_flag');
    }

    public function test_addRemovedParamAfterValidationThrows() : void
    {
        $manager = $this->createManager();
        $manager->getValidationResults();

        $this->expectException(APIException::class);
        $this->expectExceptionCode(APIException::ERROR_CANNOT_MODIFY_AFTER_VALIDATION);

        $manager->addRemovedParam('legacy_flag');
    }

    // endregion

    // region: RemovedParamCollection::detectUsed()

    public function test_detectUsedOnEmptyCollectionReturnsEmptyArray() : void
    {
        $collection = new RemovedParamCollection();

        $this->assertSame(array(), $collection->detectUsed(array('anything' => 'value')));
    }

    public function test_detectUsedIgnoresAbsentKeys() : void
    {
        $manager = $this->createManager();
        $manager->addRemovedParam('legacy_flag');

        $used = $manager->getRemovedParams()->detectUsed(array('other_param' => 'value'));

        $this->assertSame(array(), $used);
    }

    public function test_detectUsedMatchesPresentKeyWithEmptyStringValue() : void
    {
        $manager = $this->createManager();
        $manager->addRemovedParam('legacy_flag');

        $used = $manager->getRemovedParams()->detectUsed(array('legacy_flag' => ''));

        $this->assertCount(1, $used);
        $this->assertSame('legacy_flag', $used[0]->getName());
    }

    public function test_detectUsedReturnsMultipleMatchesSortedByName() : void
    {
        $manager = $this->createManager();
        $manager->addRemovedParam('zeta_flag');
        $manager->addRemovedParam('alpha_flag');

        $used = $manager->getRemovedParams()->detectUsed(array(
            'zeta_flag' => 'x',
            'alpha_flag' => 'y',
            'unrelated' => 'z'
        ));

        $this->assertCount(2, $used);
        $this->assertSame('alpha_flag', $used[0]->getName());
        $this->assertSame('zeta_flag', $used[1]->getName());
    }

    // endregion
}
