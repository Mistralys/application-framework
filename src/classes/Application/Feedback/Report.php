<?php

declare(strict_types=1);

class Application_Feedback_Report extends DBHelper_BaseRecord
{
    const COL_FEEDBACK = 'feedback';
    const COL_USER_ID = 'user_id';
    const COL_DATE = 'date';
    const COL_REQUEST_PARAMS = 'request_params';
    const COL_SCOPE = 'feedback_scope';
    const COL_TYPE = 'feedback_type';

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel() : string
    {
        return $this->getRecordStringKey('feedback');
    }
}
