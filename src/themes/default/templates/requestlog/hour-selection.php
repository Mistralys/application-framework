<?php
/**
 * File containing the template class {@see template_default_requestlog_hour_selection}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_hour_selection
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
class template_default_requestlog_hour_selection extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $grid = $this->ui->createDataGrid('requestlog-hours');
        $grid->addColumn('hour', t('Hour'));
        $grid->setEmptyMessage(t('No hours found in this day.'));

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
        $hours = $this->day->getHours();

        foreach($hours as $hour)
        {
            $entries[] = array(
                'hour' => sb()->link(
                    $hour->getLabel(),
                    $hour->getAdminURL()
                )
            );
        }

        return $entries;
    }

    /**
     * @var Application_RequestLog_LogItems_Day
     */
    private $day;

    protected function preRender(): void
    {
        $this->day = $this->getObjectVar('day', Application_RequestLog_LogItems_Day::class);
    }
}
