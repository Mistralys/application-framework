<?php
/**
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_footer
 */

declare(strict_types=1);

use Application\AppFactory;
use Application\Bootstrap\DeployCallbackBootstrap;
use AppUtils\Interface_Stringable;
use AppUtils\OutputBuffering;
use Mistralys\AppFramework\AppFramework;

/**
 * Page footer with configurable column contents.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_frame_footer extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $columns = array_keys($this->items);

        foreach($columns as $column)
        {
            $this->generateColumn($column);
        }
    }

    private function generateColumn(string $column) : void
    {
        if(empty($this->items[$column])) {
            return;
        }

        ?>
        <section>
            <h2 class="footer-column-header">
                <?php echo $column ?>
            </h2>
            <ul class="unstyled">
                <?php

                foreach($this->items[$column] as $linkDef)
                {
                    ?>
                    <li>
                        <?php
                        if(is_callable($linkDef))
                        {
                            echo $linkDef();
                        }
                        else if(is_string($linkDef))
                        {
                            echo $linkDef;
                        }
                        else if(isset($linkDef['onclick']))
                        {
                            ?>
                            <a href="#" onclick="<?php echo $linkDef['onclick'] ?>;return false">
                                <?php echo $linkDef['label'] ?>
                            </a>
                            <?php
                        }
                        else if(isset($linkDef['url']))
                        {
                            ?>
                            <a href="<?php echo $linkDef['url'] ?>">
                                <?php echo $linkDef['label'] ?>
                            </a>
                            <?php
                        }
                        ?>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </section>
        <?php
    }

    private function addDeveloperColumn() : void
    {
        if(!$this->user->isDeveloper())
        {
            return;
        }

        $this->activeColumn = t('Developer');

        $this
            ->addItemURL(t('Changelog'), APP_URL.'/'.Application_Bootstrap_Screen_Changelog::DISPATCHER)
            ->addItemURL(t('Maintenance'), APP_URL.'/'.Application_Bootstrap_Screen_Updaters::DISPATCHER_NAME)
            ->addItemURL(t('Monitoring'), APP_URL.'/xml/monitor/'.Application_Bootstrap_Screen_HealthMonitor::DISPATCHER)
            ->addItemURL(t('Run cronjob script'), APP_URL.'/'.Application_Bootstrap_Screen_Cronjobs::DISPATCHER.'?output=yes')
            ->addItemURL(t('Request log'), APP_URL.'/.'.Application_Bootstrap_Screen_RequestLog::DISPATCHER)
            ->addItemCallback(static function() : string {
                return (string)UI::button(t('Deploy callback...'))
                    ->makeLink(false)
                    ->makeConfirm(sb()
                        ->para(sb()
                            ->t('The deployment callback must only be executed once after each deployment.')
                            ->t('Executing it again may add duplicate deployment records, and overwrite stored release dates.')
                        )
                        ->para(sb()->cannotBeUndone())
                    )
                    ->link(APP_URL.'/'. DeployCallbackBootstrap::DISPATCHER_NAME);
            });

        $this->registerDeveloperItems();
    }

    /**
     * @var array<string,array<int,string|array{label:string,url:string}|array{label:string,onclick:string}|callable>>
     */
    private array $items = array();

    /**
     * @param callable $callback
     * @return $this
     */
    protected function addItemCallback(callable $callback) : self
    {
        if(!isset($this->items[$this->activeColumn])) {
            $this->items[$this->activeColumn] = array();
        }

        $this->items[$this->activeColumn][] = $callback;

        return $this;
    }

    /**
     * @param string|Interface_Stringable $label
     * @param string $url
     * @return $this
     */
    protected function addItemURL($label, string $url) : self
    {
        if(!isset($this->items[$this->activeColumn])) {
            $this->items[$this->activeColumn] = array();
        }

        $this->items[$this->activeColumn][] = array(
            'label' => (string)$label,
            'url' => $url
        );

        return $this;
    }

    /**
     * @param string|Interface_Stringable|NULL $html
     * @return $this
     */
    protected function addItemHTML($html) : self
    {
        $this->items[$this->activeColumn][] = (string)$html;
        return $this;
    }

    protected function addItemClickable($label, $statement) : self
    {
        if(!isset($this->items[$this->activeColumn])) {
            $this->items[$this->activeColumn] = array();
        }

        $this->items[$this->activeColumn][] = array(
            'label' => $label,
            'onclick' => $statement
        );

        return $this;
    }

    private string $activeColumn = '';

    final protected function preRender(): void
    {
        $this->addAppColumn();
        $this->addAboutColumn();
        $this->addAccountColumn();
        $this->addDeveloperColumn();
    }

    private function addAboutColumn() : void
    {
        $this->activeColumn = t('About');

        $this->registerAboutItems();
    }

    private function addAccountColumn() : void
    {
        $this->activeColumn = t('My account');

        $this->addItemURL(t('Settings'), $this->request->buildURL(array('page' => 'settings')));

        $this->registerAccountItems();
    }

    protected function registerAccountItems() : void
    {

    }

    protected function registerAboutItems() : void
    {

    }

    protected function registerDeveloperItems() : void
    {

    }

    protected function registerAppItems() : void
    {

    }

    private function addAppColumn() : void
    {
        $version =  $this->driver->getVersion();
        $registry = AppFactory::createDeploymentRegistry();

        $this->activeColumn = $this->driver->getAppNameShort().' '.t('v%1$s', $version);

        if($registry->versionExists($version))
        {
            $time = $registry->getByVersion($version);
            
            $this->activeColumn .= ' '.sb()->linkRight(
                (string)UI::icon()
                    ->time()
                    ->setTooltip(t(
                        'Deployed on %1$s',
                        $time->getDate()->format('Y-m-d H:i:s')
                    )),
                $registry->getAdminURLHistory(),
                Application_User::RIGHT_DEVELOPER
            );
        }

        $this->addItemClickable(t('What\'s new'), 'application.dialogWhatsnew()');

        $this->registerAppItems();

        $this->addItemCallback(static function() : string
        {
            $framework = AppFramework::getInstance();

            OutputBuffering::start();
            ?>
            <div style="padding-top:12px">
                <?php pt('Powered by:') ?><br>
                <a href="<?php echo $framework->getGithubURL() ?>" target="_blank">
                    <?php echo $framework->getName() ?>
                    v<?php echo $framework->getVersion()->getVersion() ?>
                </a>
            </div>
            <?php

            return OutputBuffering::get();
        });
    }
}