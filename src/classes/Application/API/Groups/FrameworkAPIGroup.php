<?php

declare(strict_types=1);

namespace Application\API\Groups;

class FrameworkAPIGroup extends GenericAPIGroup
{
    private function __construct()
    {
        parent::__construct(
            'framework-system-methods',
            'Framework',
            'System API methods for the application framework.'
        );
    }

    private static ?self $instance = null;

    public static function create() : self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
