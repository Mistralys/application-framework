<?php
/**
 * File containing the template class {@see template_default_requestlog_year_selection}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_year_selection
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\OutputBuffering;

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subpackage Templates
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
            <p>
            <?php
                echo UI::button(t('Clear all logs'))
                    ->setIcon(UI::icon()->delete())
                    ->makeWarning()
                    ->link($this->log->getAdminDeleteAllURL())
                    ->setTooltip(t('Deletes all stored logfiles.'))
                    ->requireTrue($this->log->hasLogs());
            ?>
            </p>
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
    private array $years;
    private Application_RequestLog $log;

    protected function preRender(): void
    {
        $this->log = AppFactory::createRequestLog();
        $this->years = $this->log->getYears();
    }
}
