<?php

declare(strict_types=1);

class Application_RequestLog_FileFilterCriteria_FileMatcher_DurationSearch extends Application_RequestLog_FileFilterCriteria_FileMatcher
{
    public function isMatch(Application_RequestLog_LogFile $file, string $search) : bool
    {
        $call = $this->valueCallback;
        $value = (float)$call($file);
        $search = str_replace(',', '.', $search);

        if(empty($search))
        {
            return false;
        }

        $sign = $search[0];
        if($sign !== '>' && $sign !== '<')
        {
            return false;
        }

        $match = (float)ltrim($search, '<>');

        if($sign === '>')
        {
            return $value > $match;
        }

        return $value < $match;
    }
}
