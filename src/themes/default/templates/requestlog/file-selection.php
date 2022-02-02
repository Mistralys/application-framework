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
        $grid = $this->ui->createDataGrid('requestlog-files');
        $grid->addColumn('time', t('Time'));
        $grid->addColumn('duration', t('Duration'))
            ->setTooltip(t('The total duration of the request.'));
        $grid->addColumn('session', t('Session ID'));
        $grid->addColumn('dispatcher', t('Dispatcher'));
        $grid->addColumn('user', t('User'));
        $grid->addColumn('size', t('Log size'));

        $grid->setEmptyMessage(t('No files found in this hour.'));

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
        $files = $this->hour->getFiles();

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
                'duration' => number_format($info->getDuration(), 2),
                'session' => $info->getSessionLabel(),
                'dispatcher' => $info->getDispatcher(),
                'user' => $info->getUserName(),
                'size' => ConvertHelper::bytes2readable($info->getLogSize())
            );
        }

        return $entries;
    }

    /**
     * @var Application_RequestLog_LogItems_Hour
     */
    private $hour;

    protected function preRender(): void
    {
        $this->hour = $this->getObjectVar('hour', Application_RequestLog_LogItems_Hour::class);
    }
}
