<?php
/**
 * File containing the template class {@see template_default_requestlog_auth}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_auth
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
class template_default_requestlog_auth extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        OutputBuffering::start();
        echo $this->renderTemplate('requestlog/header');
        ?>
        <?php echo sb()
            ->para(sb()
                ->t('The request log is only available for developers.')
            )
        ?>
        <?php echo $this->form->renderFormable() ?>
        <?php

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    /**
     * @var Application_Formable_Generic
     */
    private $form;

    protected function preRender(): void
    {
        $this->form = $this->getObjectVar('form', Application_Formable_Generic::class);
    }
}
