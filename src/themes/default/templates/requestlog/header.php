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
 * @subpackage Templates
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
            <p style="text-align: right">
                <?php
                if($this->isAuthenticated())
                {
                    ?>
                    <?php
                    echo sb()->link(
                        (string)sb()->icon(UI::icon()->home())->t('Overview'),
                        $this->log->getAdminURL()
                    );
                    ?>
                    |
                    <a href="<?php echo $this->log->getAdminSettingsURL() ?>">
                        <?php echo sb()
                            ->icon(UI::icon()->settings())
                            ->t('Settings')
                        ?>
                    </a>
                    |
                    <?php
                        echo sb()->link(
                            (string)sb()->icon(UI::icon()->list())->t('Dump info'),
                            $this->log->getAdminDumpInfoURL()
                        );
                    ?>
                    |
                    <?php
                    echo sb()->link(
                        (string)sb()->icon(UI::icon()->deleteSign())->t('Destroy session'),
                        $this->log->getAdminDestroySessionURL()
                    );
                    ?>
                    |
                    <a href="<?php echo $this->log->getAdminLogOutURL() ?>">
                        <?php echo sb()
                            ->icon(UI::icon()->logOut())
                            ->t('Log out')
                        ?>
                    </a>
                    |
                    <?php
                }
                ?>
                <a href="<?php echo APP_URL ?>">
                    <?php echo sb()
                        ->icon(UI::icon()->back())
                        ->t('Back to %1$s', $this->driver->getAppNameShort())
                    ?>
                </a>

                <br>

                <?php
                if($this->isAuthenticated())
                {
                    echo sb()
                        ->t('Logging:')
                        ->bold($status->getEnabledLabel());
                    ?>
                    (<?php
                    echo sb()->link($status->getToggleLabel(), $status->getAdminToggleURL())
                    ?>)
                    <br>
                    <?php
                }
                ?>
                <?php pt('Global developer mode:') ?>
                <?php
                if(Application_Driver::isGlobalDevelModeEnabled()) {
                    echo sb()->bold(sb()->danger(t('Active')));
                } else {
                    echo sb()->bold(sb()->muted(t('Inactive')));
                }
                ?>
            </p>
        </div>
        <h1 style="clear: both"><?php pt('Request log'); ?></h1>
        <span style="clear: both"></span>
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

    private function isAuthenticated() : bool
    {
        return $this->getVar('authenticated') !== false;
    }
}
