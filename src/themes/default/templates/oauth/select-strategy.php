<?php
/**
 * File containing the template class {@see template_default_oauth_select_strategy}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_oauth_select_strategy
 */

declare(strict_types=1);

/**
 * Screen used to select from a list of login strategies
 * (Google, GitHub, Facebook...) that can be used to log
 * in, when using the OAuth login feature.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_OAuthLogin::handleLoginScreen()
 * @see Application_OAuth
 */
class template_default_oauth_select_strategy extends UI_Page_Template_Custom
{
    /**
     * @var Application_OAuth
     */
    private $oauth;

    protected function generateOutput(): void
    {
        $sel = $this->ui->createBigSelection();
        $strategies = $this->oauth->getStrategies();

        foreach ($strategies as $strategy)
        {
            $sel->addItem($strategy->getLabel())
                ->makeLinked($strategy->getLoginURL());
        }

        echo $this->renderCleanFrame($sel->render());
    }

    protected function preRender(): void
    {
        $this->oauth = $this->getObjectVar('oauth', Application_OAuth::class);
    }
}
