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
 * @see Application_Bootstrap_Screen_RequestLog::renderMonthView()
 */
class template_default_requestlog_month_selection extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $grid = $this->ui->createDataGrid('requestlog-months');
        $grid->addColumn('month', t('Month'));
        $grid->setEmptyMessage(t('No months found in this year.'));

        OutputBuffering::start();
        ?>
            <h1><?php pt('Request log'); ?></h1>
            <?php echo $grid->render($this->collectEntries()) ?>
        <?php

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function collectEntries() : array
    {
        $entries = array();
        $months = $this->year->getMonths();

        foreach($months as $month)
        {
            $entries[] = array(
                'month' => sb()->link(
                    $month->getLabel(),
                    $month->getAdminURL()
                )
            );
        }

        return $entries;
    }

    /**
     * @var Application_RequestLog_LogItems_Year
     */
    private $year;

    protected function preRender(): void
    {
        $this->year = $this->getObjectVar('year', Application_RequestLog_LogItems_Year::class);
    }
}
