<?php
/**
 * File containing the template class {@see template_default_requestlog_day_selection}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_day_selection
 */

declare(strict_types=1);

use AppUtils\OutputBuffering;

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderDayView()
 */
class template_default_requestlog_day_selection extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $grid = $this->ui->createDataGrid('requestlog-days');
        $grid->addColumn('day', t('Day'));
        $grid->setEmptyMessage(t('No days found in this month.'));

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
        $days = $this->month->getDays();

        foreach($days as $day)
        {
            $entries[] = array(
                'day' => sb()->link(
                    $day->getLabel(),
                    $day->getAdminURL()
                )
            );
        }

        return $entries;
    }

    /**
     * @var Application_RequestLog_LogItems_Month
     */
    private $month;

    protected function preRender(): void
    {
        $this->month = $this->getObjectVar('month', Application_RequestLog_LogItems_Month::class);
    }
}
