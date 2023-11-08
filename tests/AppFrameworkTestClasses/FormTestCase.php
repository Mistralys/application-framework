<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses;

use PHPUnit\Framework\TestCase;
use UI;

abstract class FormTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_POST = array();
        $_REQUEST = array();
        $_GET = array();

        // Needed to initialize custom form elements
        UI::getInstance()->createForm('test');
    }
}
