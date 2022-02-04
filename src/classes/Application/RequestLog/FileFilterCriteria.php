<?php

declare(strict_types=1);

class Application_RequestLog_FileFilterCriteria extends Application_FilterCriteria
{
    /**
     * @var Application_RequestLog_LogItems_Hour
     */
    private $hour;

    public function __construct(Application_RequestLog_LogItems_Hour $hour, ...$args)
    {
        $this->hour = $hour;
        
        parent::__construct($hour, ...$args);
    }

    // region: Access items collection

    public function countItems() : int
    {
        return $this->hour->countFiles();
    }

    /**
     * @return Application_RequestLog_LogFile[]
     */
    public function getItems()
    {
        return $this->filterItems($this->hour->getFiles());
    }

    public function getFilesForGrid(UI_DataGrid $grid) : array
    {
        return array_slice(
            $this->getItems(),
            $grid->getOffset(),
            $grid->getLimit()
        );
    }

    // endregion

    // region: Select filter criteria

    public const FILTER_DISPATCHER = 'dispatcher';
    public const FILTER_SCREEN = 'screen';
    public const FILTER_USER_NAME = 'user_name';
    public const FILTER_SESSION_ID = 'session_id';
    public const FILTER_DURATION = 'duration';

    public function selectDispatcherSearch(string $value) : Application_RequestLog_FileFilterCriteria
    {
        return $this->selectCriteriaValue(self::FILTER_DISPATCHER, $value);
    }

    public function selectScreenSearch(string $value) : Application_RequestLog_FileFilterCriteria
    {
        return $this->selectCriteriaValue(self::FILTER_SCREEN, $value);
    }

    public function selectUserNameSearch(string $value) : Application_RequestLog_FileFilterCriteria
    {
        return $this->selectCriteriaValue(self::FILTER_USER_NAME, $value);
    }

    public function selectSessionIDSearch(string $value) : Application_RequestLog_FileFilterCriteria
    {
        return $this->selectCriteriaValue(self::FILTER_SESSION_ID, $value);
    }

    public function selectDuration(string $value) : Application_RequestLog_FileFilterCriteria
    {
        return $this->selectCriteriaValue(self::FILTER_DURATION, $value);
    }

    // endregion

    // region: Item filtering system

    /**
     * @var Application_RequestLog_FileFilterCriteria_FileMatcher_StringSearch[]|NULL
     */
    private $compiledFilters;

    /**
     * @param Application_RequestLog_LogFile[] $items
     * @return Application_RequestLog_LogFile[]
     */
    private function filterItems(array $items) : array
    {
        $this->compileFilters();

        $keep = array();
        foreach($items as $item)
        {
            if($this->isMatch($item))
            {
                $keep[] = $item;
            }
        }

        return $keep;
    }

    private function isMatch(Application_RequestLog_LogFile $file) : bool
    {
        foreach($this->compiledFilters as $filter)
        {
            if(!$this->isFilterMatch($file, $filter))
            {
                return false;
            }
        }

        return true;
    }

    private function isFilterMatch(Application_RequestLog_LogFile $file, Application_RequestLog_FileFilterCriteria_FileMatcher $filter) : bool
    {
        $criteria = $filter->getCriteriaName();

        if(!$this->hasCriteriaValues($criteria))
        {
            return true;
        }

        $values = $this->getCriteriaValues($filter->getCriteriaName());

        foreach($values as $value)
        {
            if($filter->isMatch($file, $value) === false)
            {
                return false;
            }
        }

        return true;
    }

    private function compileFilters() : void
    {
        if(isset($this->compiledFilters))
        {
            return;
        }

        $this->compileDispatcher();
        $this->compileScreen();
        $this->compileUserName();
        $this->compileSessionID();
        $this->compileDuration();
    }

    private function compileFilterStringSearch(string $setting, callable $callback) : void
    {
        $this->compiledFilters[] = new Application_RequestLog_FileFilterCriteria_FileMatcher_StringSearch(
            $setting,
            $callback
        );
    }

    // endregion

    // region: Applying item filters

    private function compileDuration() : void
    {
        $this->compiledFilters[] = new Application_RequestLog_FileFilterCriteria_FileMatcher_DurationSearch(
            self::FILTER_DURATION,
            function (Application_RequestLog_LogFile $file) : float
            {
                return $file->getFileInfo()->getDuration();
            }
        );
    }

    private function compileDispatcher() : void
    {
        $this->compileFilterStringSearch(
            self::FILTER_DISPATCHER,
            function(Application_RequestLog_LogFile $file) : string
            {
                return $file->getFileInfo()->getDispatcher();
            }
        );
    }

    private function compileScreen() : void
    {
        $this->compileFilterStringSearch(
            self::FILTER_SCREEN,
            function(Application_RequestLog_LogFile $file) : string
            {
                return $file->getFileInfo()->getScreenPath();
            }
        );
    }

    private function compileUserName() : void
    {
        $this->compileFilterStringSearch(
            self::FILTER_USER_NAME,
            function(Application_RequestLog_LogFile $file) : string
            {
                return $file->getFileInfo()->getUserName();
            }
        );
    }

    private function compileSessionID() : void
    {
        $this->compileFilterStringSearch(
            self::FILTER_SESSION_ID,
            function(Application_RequestLog_LogFile $file) : string
            {
                return $file->getFileInfo()->getSessionID();
            }
        );
    }

    // endregion
}
