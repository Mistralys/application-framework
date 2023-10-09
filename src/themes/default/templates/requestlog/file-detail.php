<?php
/**
 * File containing the template class {@see template_default_requestlog_file_detail}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_file_detail
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
class template_default_requestlog_file_detail extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        OutputBuffering::start();
        echo $this->renderTemplate('requestlog/header');

        $this->displayProperties();

        echo $this->createSection()
            ->collapse()
            ->setTitle(t('Request variables'))
            ->setContent('<pre>'.print_r($this->info->getRequestVars(), true).'</pre>');

        echo $this->createSection()
            ->collapse()
            ->setTitle(t('Server variables'))
            ->setContent('<pre>'.print_r($this->info->getServerVars(), true).'</pre>');

        echo $this->createSection()
            ->collapse()
            ->setTitle(t('Session variables'))
            ->setContent('<pre>'.print_r($this->info->getSessionVars(), true).'</pre>');

        echo $this->createSection()
            ->collapse()
            ->setTitle(t('Application log'))
            ->setContent('<pre>'.$this->info->getLog().'</pre>');

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    private function displayProperties() : void
    {
        $grid = $this->ui->createPropertiesGrid();
        $grid->addByteSize(t('Log size'), $this->info->getLogSize());
        $grid->add(t('Session ID'), $this->info->getSessionID());
        $grid->add(t('Request ID'), $this->info->getRequestID());
        $grid->add(t('User'), $this->info->getUserName());
        $grid->add(t('Time'), $this->info->getTime()->getISODate());
        $grid->add(t('Request duration'), number_format($this->info->getDuration(), 2));

        $grid->addHeader(t('Server details'));

        $grid->add(t('PHP Version'), $this->info->getPHPVersion());
        $grid->add(t('Operating system'), $this->info->getOSFamily())
            ->setComment($this->info->getOS());

        $grid->addHeader(t('Database queries'));
        $grid->addAmount(t('Total queries'), $this->info->getQueryCount());
        $grid->addAmount(t('Read queries'), $this->info->getQueryReadCount());
        $grid->addAmount(t('Write queries'), $this->info->getQueryWriteCount());

        $grid->addHeader(t('Environment'));
        $grid->addBoolean(t('Developer mode'), $this->info->isDeveloperMode());
        $grid->addBoolean(t('UI enabled'), $this->info->isUIEnabled());
        $grid->addBoolean(t('Database enabled'), $this->info->isDatabaseEnabled());
        $grid->addBoolean(t('Authentication enabled'), $this->info->isAuthEnabled());
        $grid->addBoolean(t('Session enabled'), $this->info->isNoSession());
        $grid->addBoolean(t('Session simulated'), $this->info->isSimulatedSession());
        $grid->addBoolean(t('Demo mode'), $this->info->isDemoMode());
        $grid->addBoolean(t('Command line mode'), $this->info->isCLI());

        $grid->display();
    }

    /**
     * @var Application_RequestLog_LogInfo
     */
    private $info;

    protected function preRender(): void
    {
        $file = $this->getObjectVar('file', Application_RequestLog_LogFile::class);
        $this->info = $file->getFileInfo();
    }
}
