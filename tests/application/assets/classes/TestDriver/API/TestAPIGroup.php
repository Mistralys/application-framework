<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\Groups\GenericAPIGroup;

class TestAPIGroup extends GenericAPIGroup
{
    public const string GROUP_ID = 'TestGroup';

    public function __construct()
    {
        parent::__construct(
            self::GROUP_ID,
            'Test Driver APIs',
            'All APIs provided by the Test Driver for testing purposes.'
        );
    }
}
