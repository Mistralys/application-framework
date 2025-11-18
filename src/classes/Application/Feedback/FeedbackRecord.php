<?php

declare(strict_types=1);

namespace Application\Feedback;

use DBHelper_BaseRecord;

class FeedbackRecord extends DBHelper_BaseRecord
{
    public const string COL_FEEDBACK = 'feedback';
    public const string COL_USER_ID = 'user_id';
    public const string COL_DATE = 'date';
    public const string COL_REQUEST_PARAMS = 'request_params';
    public const string COL_SCOPE = 'feedback_scope';
    public const string COL_TYPE = 'feedback_type';

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey('feedback');
    }
}
