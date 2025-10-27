<?php

declare(strict_types=1);

namespace TestDriver;

use Application\API\Groups\GenericAPIGroup;

class TestDriverAPIGroup extends GenericAPIGroup
{
    public const string GROUP_ID = 'testDriver';

    public function __construct()
    {
        parent::__construct(
            self::GROUP_ID,
            'Test application',
            'Collection of APIs used by the test application.'
        );
    }

    private static ?TestDriverAPIGroup $instance = null;

    public static function create(): TestDriverAPIGroup
    {
        if (self::$instance === null) {
            self::$instance = new TestDriverAPIGroup();
        }
        return self::$instance;
    }
}
