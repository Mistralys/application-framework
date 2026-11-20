<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Rules;

use Application\API\Parameters\Rules\RuleInterface;
use Application\API\Parameters\Rules\Type\OnlyIfOtherValueInRule;
use Application\API\Parameters\Type\StringParameter;
use Mistralys\AppFrameworkTests\TestClasses\APITestCase;

final class OnlyIfOtherValueInTest extends APITestCase
{
    public function test_targetAbsentIsAlwaysAccepted() : void
    {
        // Neither parameter is sent - the target not being present means
        // there is nothing to reject, regardless of the other's value.
        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
    }

    public function test_targetAcceptedWhenOtherValueIsAllowed() : void
    {
        $_REQUEST['target'] = 'targetValue';
        $_REQUEST['other'] = 'allowed';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed', 'also-allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
    }

    public function test_targetRejectedWhenOtherValueIsNotAllowed() : void
    {
        $_REQUEST['target'] = 'targetValue';
        $_REQUEST['other'] = 'notAllowed';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultInvalid($rule);
        $this->assertResultHasCode($rule, RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE);
    }

    public function test_targetRejectedWhenOtherIsAbsentAndNoFallback() : void
    {
        $_REQUEST['target'] = 'targetValue';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultInvalid($rule);
        $this->assertResultHasCode($rule, RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE);
    }

    public function test_fallbackResolverUsedWhenOtherIsAbsent() : void
    {
        $_REQUEST['target'] = 'targetValue';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));
        $rule->setFallbackValueResolver(static fn() : string => 'allowed');

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
    }

    public function test_fallbackResolverIgnoredWhenOtherIsPresent() : void
    {
        $_REQUEST['target'] = 'targetValue';
        $_REQUEST['other'] = 'notAllowed';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));
        // The fallback would resolve to an allowed value, but since "other"
        // is present, it must never be consulted.
        $rule->setFallbackValueResolver(static fn() : string => 'allowed');

        $rule->preValidate();
        $rule->apply();

        $this->assertResultInvalid($rule);
        $this->assertResultHasCode($rule, RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE);
    }

    public function test_skippedWhenTargetAlreadyInvalidated() : void
    {
        $_REQUEST['target'] = 'targetValue';
        $_REQUEST['other'] = 'notAllowed';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $target->invalidate();

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
    }

    public function test_skippedWhenOtherAlreadyInvalidated() : void
    {
        $_REQUEST['target'] = 'targetValue';
        $_REQUEST['other'] = 'notAllowed';

        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $other->invalidate();

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $rule->preValidate();
        $rule->apply();

        $this->assertResultValidWithNoMessages($rule);
    }

    public function test_getIDReturnsExpectedConstant() : void
    {
        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $this->assertSame('ONLY_IF_OTHER_VALUE_IN', $rule->getID());
        $this->assertSame(OnlyIfOtherValueInRule::RULE_ID, $rule->getID());
    }

    public function test_getParamsReturnsTargetAndOther() : void
    {
        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('allowed'));

        $this->assertSame(array($target, $other), $rule->getParams());
    }

    public function test_getAllowedValuesReturnsConstructorValues() : void
    {
        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('a', 'b'));

        $this->assertSame(array('a', 'b'), $rule->getAllowedValues());
    }

    public function test_renderDocumentationMentionsBothParamsAndAllowedValues() : void
    {
        $target = new StringParameter('target', 'Target');
        $other = new StringParameter('other', 'Other');

        $rule = new OnlyIfOtherValueInRule('Label', $target, $other, array('a', 'b'));

        $doc = $rule->renderDocumentation($this->createUI());

        $this->assertStringContainsString('target', $doc);
        $this->assertStringContainsString('other', $doc);
        $this->assertStringContainsString('a', $doc);
        $this->assertStringContainsString('b', $doc);
    }
}
