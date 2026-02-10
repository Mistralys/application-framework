<?php

declare(strict_types=1);

class Application_User_Statistics
{
    // ---------------------------------------------------------
    // SETTING NAME CONSTANTS
    // ---------------------------------------------------------
    // WARNING: Update the reset() method when adding new ones!

    const string SETTING_DATE_LAST_LOGIN = 'stats_date_last_login';
    const string SETTING_DATE_FIRST_LOGIN = 'stats_date_first_login';
    const string SETTING_IS_FIRST_LOGIN = 'stats_is_first_login';
    const string SETTING_TOTAL_LOGINS = 'stats_total_logins';
    const string SETTING_AMOUNT_LOGINS_TODAY = 'stats_amount_logins_today';
    const string SETTING_LOGIN_DATES = 'stats_dates_logged_in';

    /**
     * The TEXT column can store 65000 characters. Accounting
     * for JSON characters, each date takes 14 characters. This
     * means about 4600 dates can be stored.
     *
     * Still, we need to limit this to a reasonable number, so
     * a total of 2 years worth of dates should be more than
     * enough.
     */
    const MAX_LOGIN_DATES = (365 * 2);

    /**
     * @var Application_User
     */
    private $user;

    public function __construct(Application_User $user)
    {
        $this->user = $user;
    }

    /**
     * This should be called when the user has been successfully logged in.
     * It manages the statistics that are kept by user, like the amount of
     * times they logged in.
     *
     * @param DateTime $loginTime
     * @return $this
     * @throws Exception
     */
    public final function handleLoggedIn(DateTime $loginTime)
    {
        $this->calcLoginStatistics($loginTime);
        $this->user->setDateSetting(self::SETTING_DATE_LAST_LOGIN, $loginTime);

        return $this;
    }

    /**
     * Adds the current day to the list of days that
     * the user logged in.
     *
     * @param DateTime $loginTime
     */
    final protected function registerLoginDate(DateTime $loginTime) : void
    {
        $dates = $this->getDatesLoggedIn();
        $today = $loginTime->format('Y-m-d');

        if(in_array($today, $dates)) {
            return;
        }

        $dates[] = $today;

        // Ensure the list of dates does not grow beyond the
        // maximum amount of dates.
        if(count($dates) > self::MAX_LOGIN_DATES)
        {
            array_shift($dates);
        }

        $this->user->setArraySetting(self::SETTING_LOGIN_DATES, $dates);
    }

    /**
     * Retrieves a list of dates on which the user logged in,
     * from oldest to newest, in the format `Y-m-d`.
     *
     * NOTE: This does not go back forever. A limited amount
     * of dates are kept, meaning the oldest date is not
     * necessarily the user's first login date.
     *
     * @return string[]
     */
    public final function getDatesLoggedIn() : array
    {
        return $this->user->getArraySetting(self::SETTING_LOGIN_DATES);
    }

    /**
     * The amount of times the user has logged in today so far.
     *
     * @return int
     */
    public final function getAmountLoginsToday() : int
    {
        return $this->user->getIntSetting(self::SETTING_AMOUNT_LOGINS_TODAY);
    }

    /**
     * Whether this is the first time the user logged in.
     *
     * @return bool
     */
    public final function isFirstLogin() : bool
    {
        return $this->user->getBoolSetting(self::SETTING_IS_FIRST_LOGIN);
    }

    /**
     * The total amount of times the user has logged in.
     * @return int
     */
    public final function getTotalLogins() : int
    {
        return $this->user->getIntSetting(self::SETTING_TOTAL_LOGINS);
    }

    public final function getDaysSincePreviousLogin() : int
    {
        $dates = $this->getDatesLoggedIn();

        if(count($dates) < 2)
        {
            return 0;
        }

        $last = array_pop($dates); // Remove latest login
        $previous = array_pop($dates); // The login before the latest

        $interval = date_diff(
            new DateTime($previous.' 06:00:00'),
            new DateTime($last.' 06:00:00')
        );

        return intval($interval->format('%d'));
    }

    /**
     * Resets all of the user's statistics and tracking
     * information, including the date the user logged in
     * for the first time.
     */
    public final function reset() : void
    {
        $this->user->removeSetting(self::SETTING_IS_FIRST_LOGIN);
        $this->user->removeSetting(self::SETTING_DATE_FIRST_LOGIN);
        $this->user->removeSetting(self::SETTING_DATE_LAST_LOGIN);
        $this->user->removeSetting(self::SETTING_TOTAL_LOGINS);
        $this->user->removeSetting(self::SETTING_AMOUNT_LOGINS_TODAY);
        $this->user->removeSetting(self::SETTING_LOGIN_DATES);
    }

    /**
     * Handles storing all login-related statistics,
     * like the amount of times the user logged in.
     *
     * @param DateTime $loginTime
     * @throws Exception
     */
    final protected function calcLoginStatistics(DateTime $loginTime) : void
    {
        $last = $this->getLastLogin();
        $isFirstLogin = $last === null;
        $isSameDay = $last !== null && $last->format('Y-m-d') === $loginTime->format('Y-m-d');
        $loginsToday = $this->getAmountLoginsToday();

        if($isFirstLogin)
        {
            $this->user->setDateSetting(self::SETTING_DATE_FIRST_LOGIN, $loginTime);
            $loginsToday = 1;
        }
        // Already logged in today: increase the login counter for today.
        else if($isSameDay)
        {
            $loginsToday++;
        }
        // First login today.
        else
        {
            $loginsToday = 1; // Reset in case there was an older value
        }

        $this->user->setIntSetting(self::SETTING_AMOUNT_LOGINS_TODAY, $loginsToday);
        $this->user->setBoolSetting(self::SETTING_IS_FIRST_LOGIN, $isFirstLogin);
        $this->user->setIntSetting(self::SETTING_TOTAL_LOGINS, $this->getTotalLogins() + 1);

        $this->registerLoginDate($loginTime);
    }

    /**
     * Gets the last time the user has logged in.
     *
     * @return DateTime|null
     * @throws Exception
     */
    public final function getLastLogin() : ?DateTime
    {
        return $this->user->getDateSetting(self::SETTING_DATE_LAST_LOGIN);
    }

    /**
     * Gets the time the user logged in for the first time.
     *
     * @return DateTime|null
     * @throws Exception
     */
    public final function getFirstLogin() : ?DateTime
    {
        return $this->user->getDateSetting(self::SETTING_DATE_FIRST_LOGIN);
    }
}
