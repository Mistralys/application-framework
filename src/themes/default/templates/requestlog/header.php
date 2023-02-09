<?php
/**
 * File containing the template class {@see template_default_requestlog_header}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_requestlog_header
 */

declare(strict_types=1);

use Application\AppFactory;

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
        $status = $this->log->getStatus();

        ?>
        <div class="pull-right">
            <p>
                <?php echo sb()
                    ->t('Logging:')
                    ->bold($status->getEnabledLabel());
                ?>
                (<?php
                echo sb()->link($status->getToggleLabel(), $status->getAdminToggleURL())
                ?>)
                |
                <a href="<?php echo AppFactory::createRequestLog()->getAdminLogOutURL() ?>">
                    <?php echo sb()
                        ->icon(UI::icon()->logOut())
                        ->t('Log out')
                    ?>
                </a>
                |
                <a href="<?php echo APP_URL ?>">
                    <?php echo sb()
                        ->icon(UI::icon()->back())
                        ->t('Back to %1$s', $this->driver->getAppNameShort())
                    ?>
                </a>
            </p>
        </div>
        <h1><?php pt('Request log'); ?></h1>
        <?php
        echo $this->page->renderMessages();
        echo $this->page->getBreadcrumb()->render();
        ?><hr/><?php
    }

    private Application_RequestLog $log;

    protected function preRender(): void
    {
        $this->log = AppFactory::createRequestLog();
    }
}
