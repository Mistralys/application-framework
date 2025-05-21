<?php

declare(strict_types=1);

use Application\Interfaces\Admin\AdminScreenInterface;

class Application_User_ScreenTracker
{
    const SETTING_SCREEN_COUNTER = 'tracker_screen_counter';

    /**
     * @var Application_User
     */
    private $user;

    public function __construct(Application_User $user)
    {
        $this->user = $user;
    }

    public function handleScreenAccessed(AdminScreenInterface $screen) : void
    {
        $counters = $this->user->getArraySetting(self::SETTING_SCREEN_COUNTER);
        $path = $screen->getURLPath();

        if(!isset($counters[$path]))
        {
            $counters[$path] = array(
                'count' => 0,
                'title' => ''
            );
        }

        $counters[$path]['count']++;
        $counters[$path]['title'] = $screen->getTitle();

        $this->user->setArraySetting(self::SETTING_SCREEN_COUNTER, $counters);
        $this->user->saveSettings();
    }
}
