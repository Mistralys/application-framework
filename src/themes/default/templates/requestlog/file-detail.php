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
            ->setTitle(t('Application log'))
            ->setContent('<pre>'.$this->info->getLog().'</pre>');

        echo $this->renderCleanFrame(OutputBuffering::get());
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
