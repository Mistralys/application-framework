<?php
/**
 * File containing the template class {@see template_default_ui_nav_search_full_width}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_ui_nav_search_full_width
 */

declare(strict_types=1);

/**
 * Template for the navigation search widget.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page_Navigation_Item_Search
 */
class template_default_ui_nav_search_full_width extends template_default_ui_nav_search_inline
{
    const ERROR_NO_SCOPES_AVAILABLE = 81101;

    /**
     * @var string
     */
    private $scopeELID;

    protected function generateOutput() : void
    {
        if(empty($this->scopes))
        {
            throw new UI_Themes_Exception(
                'The full width search bar can only be used with scopes.',
                '',
                self::ERROR_NO_SCOPES_AVAILABLE
            );
        }

        // Generate an ID for the hidden input storing the selected scope
        $this->scopeELID = nextJSID();
        
        ?>
            <form method="post" class="<?php echo implode(' ', $this->search->getClasses()) ?>">
                <?php $this->renderHiddens() ?>
                <?php $this->renderTabs() ?>
                <?php $this->renderScopes() ?>
            </form>
        <?php
    }

    /**
     * @throws Application_Exception
     */
    protected function renderTabs() : void
    {
        $tabs = $this->ui->createTabs('search_tabs_'.$this->search->getName());

        foreach($this->scopes as $scope)
        {
            $tabs->appendTab($scope['label'], $scope['name'])
                ->setContent($this->renderTab($scope))
                ->clientOnSelect($this->renderTabClickHandler($scope['name']));
        }

        $tabs->selectTab($tabs->getTabByName($this->search->getSelectedScopeID()));

        ?>
            <div class="search-fullwidth-tabs">
                <?php $tabs->display(); ?>
            </div>
        <?php
    }

    /**
     * Renders the javascript click handler that is called when a tab
     * is activated: Selects the according scope, and focuses on the
     * target search element.
     *
     * @param string $scopeID
     * @return string
     */
    private function renderTabClickHandler(string $scopeID) : string
    {
        return sprintf(
            "$('#%s').val('%s');$('[name=\'%s\']').focus();",
            $this->scopeELID,
            $scopeID,
            $this->search->getSearchElementName($scopeID)
        );
    }

    private function renderTab(array $scope) : string
    {
        ob_start();
        ?>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <input  name="<?php echo $this->search->getSearchElementName($scope['name']) ?>"
                                    type="text"
                                    class="search-input-terms scope-<?php echo $scope['name'] ?>"
                                    placeholder="<?php pt('Search...') ?>"
                                    value="<?php echo $this->search->getSearchTerms($scope['name']) ?>"/>
                        </td>
                        <td>
                            <?php
                                $this->renderCountrySelection($scope['name']);
                            ?>
                        </td>
                        <td>
                            <?php
                                UI::button()
                                    ->setIcon(UI::icon()->search())
                                    ->makeSubmit('run_search', 'yes')
                                    ->display();
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php

        return ob_get_clean();
    }

    protected function renderScopes() : void
    {
        if(empty($this->scopes)) {
            return;
        }

        ?>
            <input  type="hidden"
                    id="<?php echo $this->scopeELID ?>"
                    name="<?php echo $this->search->getScopeElementName() ?>"
                    value="<?php echo $this->search->getSelectedScopeID() ?>">
        <?php
    }
}
