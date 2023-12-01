<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\UI\Forms\CustomElements;

use AppFrameworkTestClasses\ApplicationTestCase;
use UI;
use UI_Form;

/**
 * @package Application
 * @subpackage UnitTests
 */
final class UIButtonTest extends ApplicationTestCase
{
    // region: _Tests

    public function test_createElement() : void
    {
        $this->testForm->addButton('test-button');

        // No exception means the element could be created
        $this->addToAssertionCount(1);
    }

    public function test_setValues() : void
    {
        $btn = $this->testForm->addButton('super-button')
            ->setLabel('My label');

        $this->assertSame('My label', $btn->getLabel());
    }

    public function test_render() : void
    {
        $html = (string)$this->testForm->addButton('super-button')
            ->setLabel('My label')
            ->setTitle('My title')
            ->setLoadingText('Load me');

        $this->assertStringContainsString('super-button', $html);
        $this->assertStringContainsString('My label', $html);
        $this->assertStringContainsString('My title', $html);
        $this->assertStringContainsString('Load me', $html);
    }

    // endregion

    // region: Support methods

    private UI_Form $testForm;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testForm = UI::getInstance()->createForm('ui-button-test-'.$this->getTestCounter());

        $this->assertFalse($this->testForm->isSubmitted());
    }

    // endregion
}
