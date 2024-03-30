<?php

declare(strict_types=1);

namespace Application\Tags;

use Application_Exception;

class TaggingException extends Application_Exception
{
    public const ERROR_ROOT_TAG_NOT_SET = 153701;
}
