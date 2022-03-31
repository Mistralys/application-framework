<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\UI\Forms\CustomElements;

use ApplicationTestCase;
use UI;

/**
 * @package Application
 * @subpackage UnitTests
 */
class UIButtonTest extends ApplicationTestCase
{
    public function test_createElement() : void
    {
        $form = UI::getInstance()->createForm('uibutton-test');

        $form->addButton('test-button');

        // No exception means the element could be created
        $this->addToAssertionCount(1);
    }
}
