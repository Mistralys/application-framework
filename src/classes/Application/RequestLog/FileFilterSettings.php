<?php

declare(strict_types=1);

/**
 * @property Application_RequestLog_FileFilterCriteria $filters
 */
class Application_RequestLog_FileFilterSettings extends Application_FilterSettings
{
    public const SETTING_DISPATCHER = 'dispatcher';
    public const SETTING_SCREEN = 'screen';
    public const SETTING_USER_NAME = 'user_name';
    public const SETTING_SESSION_ID = 'session_id';
    public const SETTING_DURATION = 'duration';

    public function __construct()
    {
        parent::__construct('request-log-files');
    }

    /**
     * @return void
     * @see Application_RequestLog_FileFilterSettings::inject_dispatcher()
     * @see Application_RequestLog_FileFilterSettings::inject_screen()
     * @see Application_RequestLog_FileFilterSettings::inject_user_name()
     * @see Application_RequestLog_FileFilterSettings::inject_session_id()
     * @see Application_RequestLog_FileFilterSettings::inject_duration()
     */
    protected function registerSettings() : void
    {
        $this->registerSetting(self::SETTING_DISPATCHER, t('Dispatcher'));
        $this->registerSetting(self::SETTING_SCREEN, t('Screen'));
        $this->registerSetting(self::SETTING_USER_NAME, t('User name'));
        $this->registerSetting(self::SETTING_SESSION_ID, t('Session ID'));
        $this->registerSetting(self::SETTING_DURATION, t('Duration'));
    }

    public function inject_dispatcher() : void
    {
        $el = $this->addElementText(self::SETTING_DISPATCHER);
        $el->addFilterTrim();
        $el->setComment((string)sb()
            ->t('Searches in the dispatcher file name.')
            ->t('Case insensitive.')
        );
    }

    public function inject_screen() : void
    {
        $el = $this->addElementText(self::SETTING_SCREEN);
        $el->addFilterTrim();
        $el->setComment((string)sb()
            ->t('Searches in the screen path.')
            ->t('Case insensitive.')
        );
    }

    public function inject_user_name() : void
    {
        $el = $this->addElementText(self::SETTING_USER_NAME);
        $el->addFilterTrim();
        $el->setComment((string)sb()
            ->t('Searches in the user name.')
            ->t('Case insensitive.')
        );
    }

    public function inject_session_id() : void
    {
        $el = $this->addElementText(self::SETTING_SESSION_ID);
        $el->addFilterTrim();
        $el->setComment((string)sb()
            ->t('Searches in the session ID.')
            ->t('Case insensitive.')
            ->t('Special values are:')
            ->ul(array(
                sb()
                    ->code(Application_RequestLog::SESSION_ID_SIMULATED)
                    ->t('Simulated session'),
                sb()
                    ->code(Application_RequestLog::SESSION_ID_NONE)
                    ->t('No session used')
            ))
        );
    }

    public function inject_duration() : void
    {
        $el = $this->addElementText(self::SETTING_DURATION);
        $el->addFilterTrim();
        $el->addClass('input-small');
        $el->setComment((string)sb()
            ->t('Limits to files that match the specified duration.')
            ->t('Examples:')
            ->ul(array(
                sb()->code('> 1')->t('Longer than a second.'),
                sb()->code('< 2')->t('Shorter than two seconds.'),
                sb()->code('> 0.85')->t('Precision up to two decimals.')
            ))
        );
    }

    protected function _configureFilters() : void
    {
        $this->configureDispatcher();
        $this->configureScreen();
        $this->configureUserName();
        $this->configureSessionID();
        $this->configureDuration();
    }

    private function configureDispatcher() : void
    {
        $value = $this->getSettingString(self::SETTING_DISPATCHER);

        if(!empty($value))
        {
            $this->filters->selectDispatcherSearch($value);
        }
    }

    private function configureScreen() : void
    {
        $value = $this->getSettingString(self::SETTING_SCREEN);

        if(!empty($value))
        {
            $this->filters->selectScreenSearch($value);
        }
    }

    private function configureUserName() : void
    {
        $value = $this->getSettingString(self::SETTING_USER_NAME);

        if(!empty($value))
        {
            $this->filters->selectUserNameSearch($value);
        }
    }

    private function configureSessionID() : void
    {
        $value = $this->getSettingString(self::SETTING_SESSION_ID);

        if(!empty($value))
        {
            $this->filters->selectSessionIDSearch($value);
        }
    }

    private function configureDuration() : void
    {
        $value = $this->getSettingString(self::SETTING_DURATION);

        if(!empty($value))
        {
            $this->filters->selectDuration($value);
        }
    }
}
