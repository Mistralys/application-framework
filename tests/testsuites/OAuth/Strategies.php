<?php

declare(strict_types=1);

use Mistralys\AppFrameworkTests\TestClasses\ApplicationTestCase;

/**
 * The test driver has exactly two strategies configured:
 * `Google` and `GitHub`.
 *
 * @see TestDriver_OAuth_GitHub
 * @see TestDriver_OAuth_Google
 */
final class OAuth_StrategiesTest extends ApplicationTestCase
{
    /**
     * @var Application_OAuth
     */
    private $oauth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oauth = Application_Driver::getInstance()->createOAuth();
    }

    public function test_strategyExists() : void
    {
        $this->startTest('Strategy exists?');

        $this->assertTrue($this->oauth->strategyExists('Google'));
        $this->assertTrue($this->oauth->strategyExists('GitHub'));

        $this->assertFalse($this->oauth->strategyExists('google'));
    }

    public function test_createUnknown() : void
    {
        $this->startTest('Trying to create an unknown strategy');

        try
        {
            $this->oauth->createStrategy('Unknown');
        }
        catch (OAuth_Exception $e)
        {
            $this->assertEquals(Application_OAuth::ERROR_STRATEGY_CLASS_NOT_FOUND, $e->getCode());
            return;
        }

        $this->fail('No exception triggered.');
    }

    public function test_getStrategies(): void
    {
        if($this->skipIfRunViaApplication()) {
            return;
        }

        $this->startTest('Getting all strategies');

        $strategies = $this->oauth->getStrategies();

        $this->assertCount(2, $strategies);
    }

    public function test_getAvailable() : void
    {
        $this->startTest('Getting framework strategy names');

        $names = $this->oauth->getAvailableNames();

        $this->assertTrue(count($names) >= 3);

        $this->assertContains('Google', $names);
        $this->assertContains('GitHub', $names);
        $this->assertContains('Facebook', $names);
    }

    public function test_hasStrategies() : void
    {
        if($this->skipIfRunViaApplication()) {
            return;
        }

        $this->startTest('Are strategies enabled?');

        $this->assertTrue($this->oauth->hasStrategies());
    }

    public function test_isStrategyEnabled() : void
    {
        if($this->skipIfRunViaApplication()) {
            return;
        }

        $this->startTest('Is strategy enabled?');

        $this->assertTrue($this->oauth->isStrategyEnabled('Google'));
        $this->assertTrue($this->oauth->isStrategyEnabled('GitHub'));
        $this->assertFalse($this->oauth->isStrategyEnabled('Facebook'));
    }

    public function test_getByName() : void
    {
        if($this->skipIfRunViaApplication()) {
            return;
        }

        $this->startTest('Get strategy by name');

        $strategy = $this->oauth->getByName('Google');

        $this->assertInstanceOf(TestDriver_OAuth_Google::class, $strategy);
    }

    public function test_getByName_notExists() : void
    {
        $this->startTest('Get strategy by name');

        try
        {
            // Facebook exists, but is not enabled for the application.
            $this->oauth->getByName('Facebook');
        }
        catch(OAuth_Exception $e)
        {
            $this->assertEquals(Application_OAuth::ERROR_UNKNOWN_STRATEGY, $e->getCode());
            return;
        }

        $this->fail('No exception thrown.');
    }
}
