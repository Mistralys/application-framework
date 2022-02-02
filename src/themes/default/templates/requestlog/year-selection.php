<?php
/**
 * File containing the template class {@see template_default_requestlog_year_selection}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_year_selection
 */

declare(strict_types=1);

use AppUtils\OutputBuffering;

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderYearNav()
 */
class template_default_requestlog_year_selection extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $grid = $this->ui->createDataGrid('requestlog-years');
        $grid->addColumn('year', t('Year'));
        $grid->setEmptyMessage(t('No request logs have been found.'));

        OutputBuffering::start();
        echo $this->renderTemplate('requestlog/header');
        ?>
            <?php echo $grid->render($this->collectEntries()) ?>
        <?php

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function collectEntries() : array
    {
        $entries = array();

        foreach($this->years as $year)
        {
            $entries[] = array(
                'year' => sb()->link(
                    (string)$year->getYearNumber(),
                    $year->getAdminURL()
                )
            );
        }

        return $entries;
    }

    /**
     * @var Application_RequestLog_LogItems_Year[]
     */
    private $years;

    protected function preRender(): void
    {
        $this->years = Application::createRequestLog()->getYears();
    }
}
