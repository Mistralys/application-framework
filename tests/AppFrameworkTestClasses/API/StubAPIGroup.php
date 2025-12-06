<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\API;

use Application\API\Groups\GenericAPIGroup;

class StubAPIGroup extends GenericAPIGroup
{
    public const string GROUP_NAME = 'StubAPI';

    public function __construct()
    {
        parent::__construct(
            self::GROUP_NAME,
            'Stub APIs',
            'Group for stub API methods used in testing.'
        );
    }
}
