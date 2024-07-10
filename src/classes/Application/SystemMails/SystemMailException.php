<?php

declare(strict_types=1);

namespace Application\SystemMails;

use Application_Exception;

class SystemMailException extends Application_Exception
{
    public const ERROR_NO_BODY_CONTENT = 159601;
    public const ERROR_NO_SUBJECT_SET = 159602;
}
