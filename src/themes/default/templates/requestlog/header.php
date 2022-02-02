<?php
/**
 * File containing the template class {@see template_default_requestlog_header}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_header
 */

declare(strict_types=1);

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog::renderMonthView()
 */
class template_default_requestlog_header extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        ?>
        <p class="pull-right">
            <a href="<?php echo Application::createRequestLog()->getAdminLogOutURL() ?>">
                <?php echo UI::icon()->logOut() ?>
                <?php pt('Log out') ?>
            </a>
        </p>
        <h1><?php pt('Request log'); ?></h1>
        <?php
        echo $this->page->renderMessages();
        echo $this->page->getBreadcrumb()->render();
        ?><hr/><?php
    }

    protected function preRender(): void
    {
    }
}
