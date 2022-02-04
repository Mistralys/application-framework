<?php

declare(strict_types=1);

/**
 * @see Application_RequestLog
 */
class Application_Bootstrap_Screen_RequestLog extends Application_Bootstrap_Screen
{
    public const REQUEST_PARAM_YEAR = 'year';
    public const REQUEST_PARAM_MONTH = 'month';
    public const REQUEST_PARAM_DAY = 'day';
    public const REQUEST_PARAM_HOUR = 'hour';
    public const REQUEST_PARAM_ID = 'requestID';
    public const REQUEST_PARAM_LOG_OUT = 'log_out';
    public const REQUEST_PARAM_TOGGLE_STATUS = 'set_status';

    public const DISPATCHER = 'requestlog.php';
    public const SESSION_AUTH_PARAM = 'requestlog_authenticated';

    /**
     * @var Application_RequestLog
     */
    private $log;

    /**
     * @var UI_Page
     */
    private $page;

    /**
     * @var Application_Request
     */
    private $request;

    private $persistVars = array();

    public function getDispatcher()
    {
        return self::DISPATCHER;
    }

    protected function _boot()
    {
        $this->disableAuthentication();
        $this->createEnvironment();

        $this->log = Application::createRequestLog();
        $this->page = $this->driver->getUI()->createPage('request-log');
        $this->request = Application_Driver::getInstance()->getRequest();
        $breadcrumb = $this->page->getBreadcrumb();

        if($this->request->getBool(self::REQUEST_PARAM_LOG_OUT))
        {
            $this->handleLogOut();
        }

        if($this->request->hasParam(self::REQUEST_PARAM_TOGGLE_STATUS))
        {
            $this->handleStatusChange($this->request->getBool(self::REQUEST_PARAM_TOGGLE_STATUS));
        }

        if($this->session->getValue(self::SESSION_AUTH_PARAM) !== 'yes')
        {
            $this->handleAuthentication();
        }

        if(!$this->request->hasParam(self::REQUEST_PARAM_YEAR))
        {
            displayHTML($this->renderYearNav());
        }

        $year = $this->log->getYearByNumber((int)$this->request->getParam(self::REQUEST_PARAM_YEAR));
        $breadcrumb->appendItem($year->getLabel())->makeLinked($year->getAdminURL());
        $this->persistVars[self::REQUEST_PARAM_YEAR] = $year->getYearNumber();

        if(!$this->request->hasParam(self::REQUEST_PARAM_MONTH))
        {
            displayHTML($this->renderMonthView($year));
        }

        $month = $year->getMonthByNumber((int)$this->request->getParam(self::REQUEST_PARAM_MONTH));
        $breadcrumb->appendItem($month->getLabel())->makeLinked($month->getAdminURL());
        $this->persistVars[self::REQUEST_PARAM_MONTH] = $month->getMonthNumber();

        if(!$this->request->hasParam(self::REQUEST_PARAM_DAY))
        {
            displayHTML($this->renderDayView($month));
        }

        $day = $month->getDayByNumber((int)$this->request->getParam(self::REQUEST_PARAM_DAY));
        $breadcrumb->appendItem($day->getLabel())->makeLinked($day->getAdminURL());
        $this->persistVars[self::REQUEST_PARAM_DAY] = $day->getDayNumber();

        if(!$this->request->hasParam(self::REQUEST_PARAM_HOUR))
        {
            displayHTML($this->renderHourView($day));
        }

        $hour = $day->getHourByNumber((int)$this->request->getParam(self::REQUEST_PARAM_HOUR));
        $breadcrumb->appendItem($hour->getLabel())->makeLinked($hour->getAdminURL());
        $this->persistVars[self::REQUEST_PARAM_HOUR] = $hour->getHourNumber();

        if(!$this->request->hasParam(self::REQUEST_PARAM_ID))
        {
            displayHTML($this->renderFileSelectionView($hour));
        }

        $file = $hour->getFileByRequestID((string)$this->request->getParam(self::REQUEST_PARAM_ID));
        $breadcrumb->appendItem($file->getLabel())->makeLinked($file->getAdminURL());
        $this->persistVars[self::REQUEST_PARAM_ID] = $file->getRequestID();

        displayHTML($this->renderFileDetailView($file));

        Application::exit();
    }

    private function handleLogOut() : void
    {
        $this->session->setValue(self::SESSION_AUTH_PARAM, 'no');

        UI::getInstance()->addSuccessMessage(t('You have been successfully logged out.'));

        Application::redirect($this->log->getAdminURL());
    }

    private function handleStatusChange(bool $enable) : void
    {
        $this->log->getStatus()->setEnabled($enable);

        if($enable)
        {
            $message = t('The request logging has been enabled.');
        }
        else
        {
            $message = t('The request logging has been disabled.');
        }

        UI::getInstance()->addSuccessMessage($message);

        Application::redirect($this->log->getAdminURL());
    }

    private function handleAuthentication() : void
    {
        $form = $this->createForm();

        if($form->isFormValid())
        {
            $this->session->setValue(self::SESSION_AUTH_PARAM, 'yes');
            Application::redirect(APP_URL.'/'.$this->getDispatcher());
        }

        displayHTML(
            $this->renderAuthentication($form)
        );
    }

    private function createForm() : Application_Formable_Generic
    {
        $form = new Application_Formable_Generic();
        $form->createFormableForm('requestlog-authentication');

        $el = $form->addElement('password', 'auth_secret');
        $el->addFilter('trim');
        $el->setLabel(t('Auth token'));

        $form->addRuleCallback(
            $el,
            array($this, 'callback_validatePassword'),
            t('Invalid auth token.')
        );

        $form->makeRequired($el);

        $form->getFormInstance()->addPrimarySubmit((string)sb()
            ->icon(UI::icon()->logIn())
            ->t('Authenticate')
        );

        $form->setDefaultElement($el->getName());

        return $form;
    }

    public function callback_validatePassword(string $value) : bool
    {
        if(empty($value))
        {
            return true;
        }

        return $value === 'tomato';
    }

    private function renderAuthentication(Application_Formable_Generic $form) : string
    {
        return $this->page->renderTemplate(
            'requestlog/auth',
            array(
                'form' => $form
            )
        );
    }

    /**
     * @return string
     * @throws Application_Exception
     * @see template_default_requestlog_year_selection
     */
    private function renderYearNav() : string
    {
        return $this->page->renderTemplate(
            'requestlog/year-selection'
        );
    }

    /**
     * @param Application_RequestLog_LogItems_Year $year
     * @return string
     * @throws Application_Exception
     * @see template_default_requestlog_month_selection
     */
    private function renderMonthView(Application_RequestLog_LogItems_Year $year) : string
    {
        return $this->page->renderTemplate(
            'requestlog/month-selection',
            array(
                'year' => $year
            )
        );
    }

    /**
     * @param Application_RequestLog_LogItems_Month $month
     * @return string
     * @throws Application_Exception
     * @see template_default_requestlog_day_selection
     */
    private function renderDayView(Application_RequestLog_LogItems_Month $month) : string
    {
        return $this->page->renderTemplate(
            'requestlog/day-selection',
            array(
                'month' => $month
            )
        );
    }

    /**
     * @param Application_RequestLog_LogItems_Day $day
     * @return string
     * @throws Application_Exception
     * @see template_default_requestlog_hour_selection
     */
    private function renderHourView(Application_RequestLog_LogItems_Day $day) : string
    {
        return $this->page->renderTemplate(
            'requestlog/hour-selection',
            array(
                'day' => $day
            )
        );
    }

    /**
     * @param Application_RequestLog_LogItems_Hour $hour
     * @return string
     * @throws Application_Exception
     * @see template_default_requestlog_file_selection
     */
    private function renderFileSelectionView(Application_RequestLog_LogItems_Hour $hour) : string
    {
        return $this->page->renderTemplate(
            'requestlog/file-selection',
            array(
                'hour' => $hour,
                'screen' => $this
            )
        );
    }

    private function renderFileDetailView(Application_RequestLog_LogFile $file) : string
    {
        return $this->page->renderTemplate(
            'requestlog/file-detail',
            array(
                'file' => $file
            )
        );
    }

    public function getPersistVars() : array
    {
        return $this->persistVars;
    }
}
