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
 * Template for the request log settings screen.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderSettings()
 */
class template_default_requestlog_settings extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        OutputBuffering::start();

        echo $this->renderTemplate('requestlog/header');
        ?>
        <h3><?php pt('Settings') ?></h3>
        <?php
        echo $this->getVar('form');
        ?>

        <?php

        echo $this->renderCleanFrame(OutputBuffering::get());
    }

    protected function preRender(): void
    {
    }
}
