<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Exception\ApplicationException;

class RevisionableException extends ApplicationException
{
    public const int ERROR_CANNOT_DELETE_RECORD_DIRECTLY = 16104;
    public const int ERROR_INVALID_MULTI_ACTION_CLASS = 16101;
    public const int ERROR_CANNOT_DESTROY_RECORD = 16103;
    public const int ERROR_REVISION_DOES_NOT_EXIST = 16102;
    public const int ERROR_INVALID_CREATE_ARGUMENTS = 16105;
    public const int ERROR_CANNOT_USE_SET_RECORD_KEY = 16106;
    public const int ERROR_REVISIONABLE_NOT_AVAILABLE = 16107;
}
