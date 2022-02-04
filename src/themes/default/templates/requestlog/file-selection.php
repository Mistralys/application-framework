<?php
/**
 * File containing the template class {@see template_default_requestlog_file_selection}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_file_selection
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\OutputBuffering;

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderHourView()
 */
class template_default_requestlog_file_selection extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        OutputBuffering::start();

        echo $this->renderTemplate('requestlog/header');
        ?>
            <?php echo $this->grid->render($this->collectEntries()) ?>
        <?php

        echo $this->form->render();

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    /**
     * @var Application_RequestLog_LogItems_Hour
     */
    private $hour;

    /**
     * @var UI_DataGrid
     */
    private $grid;

    /**
     * @var Application_RequestLog_FileFilterSettings
     */
    private $form;

    /**
     * @var Application_Bootstrap_Screen_RequestLog
     */
    private $screen;

    protected function preRender(): void
    {
        $this->hour = $this->getObjectVar('hour', Application_RequestLog_LogItems_Hour::class);
        $this->screen = $this->getObjectVar('screen', Application_Bootstrap_Screen_RequestLog::class);

        $this->createGrid();
        $this->createForm();
    }

    private function createGrid() : void
    {
        $grid = $this->ui->createDataGrid('request-log-files');
        $grid->addColumn('time', t('Time'))
            ->alignRight();
        $grid->addColumn('duration', t('Duration'))
            ->setTooltip(t('The total duration of the request.'))
            ->alignRight();
        $grid->addColumn('session', t('Session ID'));
        $grid->addColumn('dispatcher', t('Dispatcher'));
        $grid->addColumn('screen', t('Screen'));
        $grid->addColumn('user', t('User'));
        $grid->addColumn('size', t('Log size'))
            ->alignRight();

        $grid->setEmptyMessage(t('No files found in this hour.'));

        $grid->enableLimitOptionsDefault();

        $grid->setDispatcher(Application_Bootstrap_Screen_RequestLog::DISPATCHER);
        $grid->addHiddenVars($this->screen->getPersistVars());

        $this->grid = $grid;
    }

    private function collectEntries() : array
    {
        $entries = array();
        $criteria = $this->hour->createFilterCriteria();

        $this->grid->configure($this->form, $criteria);

        $files = $criteria->getFilesForGrid($this->grid);

        foreach($files as $file)
        {
            $info = $file->getFileInfo();

            $entries[] = array(
                'time' => sb()
                    ->link(
                        $file->getLabel(),
                        $file->getAdminURL()
                    )
                    ->muted(sprintf(
                        '%s ms',
                        $info->getMicroseconds()
                    )),
                'duration' => number_format($info->getDuration(), 6),
                'session' => $info->getSessionLabel(),
                'dispatcher' => $info->getDispatcher(),
                'screen' => $info->getScreenPath(),
                'user' => $info->getUserName(),
                'size' => ConvertHelper::bytes2readable($info->getLogSize())
            );
        }

        return $entries;
    }

    private function createForm() : void
    {
        $this->form = $this->hour->createFilterSettings();
        $this->form->addHiddenVars($this->screen->getPersistVars());
    }
}
