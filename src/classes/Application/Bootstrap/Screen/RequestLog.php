<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_RequestLog extends Application_Bootstrap_Screen
{
    public const REQUEST_PARAM_YEAR = 'year';
    public const REQUEST_PARAM_MONTH = 'month';

    public const DISPATCHER = 'requestlog.php';

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

    public function getDispatcher()
    {
        return self::DISPATCHER;
    }

    protected function _boot()
    {
        $this->disableAuthentication();
        $this->createEnvironment();

        if(boot_constant('APP_WRITE_LOG') !== true)
        {
            die((string)sb()
                ->para(
                'Request log viewer is disabled: Log writing is deactivated.'
                )
                ->para(
                    'Please refer to the framework documentation on debugging for details.'
                )
            );
        }

        $this->log = Application::createRequestLog();
        $this->page = $this->driver->getUI()->createPage('request-log');
        $this->request = Application_Driver::getInstance()->getRequest();


        if(!$this->request->hasParam(self::REQUEST_PARAM_YEAR))
        {
            displayHTML($this->renderYearNav());
        }

        $year = $this->log->getYearByNumber((int)$this->request->getParam(self::REQUEST_PARAM_YEAR));

        if(!$this->request->hasParam('month'))
        {
            displayHTML($this->renderMonthView($year));
        }

        Application::exit();
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
}
