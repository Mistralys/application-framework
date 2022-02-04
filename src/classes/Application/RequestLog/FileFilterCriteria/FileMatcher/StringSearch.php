<?php

declare(strict_types=1);

class Application_RequestLog_FileFilterCriteria_FileMatcher_StringSearch extends Application_RequestLog_FileFilterCriteria_FileMatcher
{
    public function isMatch(Application_RequestLog_LogFile $file, string $search) : bool
    {
        $call = $this->valueCallback;
        $value = (string)$call($file);

        return stripos($value, $search) !== false;
    }
}
