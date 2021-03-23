<?php
/**
 * File containing the template class {@see template_default_ui_nav_search_inline}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_ui_nav_search_inline
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
class template_default_ui_nav_search_inline extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        ?>
            <form method="post" class="nav-search <?php echo implode(' ', $this->search->getClasses()) ?>">
                <?php $this->renderHiddens() ?>
                <div class="search-inputs">
                    <input  name="<?php echo $this->search->getSearchElementName($this->scopeID) ?>"
                            type="text" class="search-input search-input-terms"
                            placeholder="<?php pt('Search...') ?>"
                            value="<?php echo $this->search->getSearchTerms() ?>"/>
                    <?php $this->renderScopes() ?>
                </div>
                <div class="search-button">
                    <?php
                        UI::button()
                            ->setIcon(UI::icon()->search())
                            ->makeSubmit('run_search', 'yes')
                            ->display();
                    ?>
                </div>
            </form>
        <?php
    }

    protected function renderScopes() : void
    {
        if(empty($this->scopes)) {
            return;
        }

        ?>
            <select name="scope" class="search-input search-input-scope">
                <?php
                    foreach($this->scopes as $scope)
                    {
                        ?>
                            <option value="<?php echo $scope['name'] ?>">
                                <?php echo $scope['label'] ?>
                            </option>
                        <?php
                    }
                ?>
            </select>
        <?php
    }

    protected function renderHiddens() : void
    {
        $hiddens = $this->search->getHiddenVars();

        ?>
            <div class="form-hiddens" style="display:none">
                <?php
                    foreach($hiddens as $name => $value)
                    {
                        ?>
                            <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>"/>
                        <?php
                    }
                ?>
            </div>
        <?php
    }

    protected function renderRegionSelection(string $scope)
    {
        if(!$this->search->hasRegionSelectionEnabled()) {
            return;
        }

        $persistedRegion = $this->search->getPersistVars()[$this->search->getRegionSelectionElementName($scope)];

        ?>
            <div class="search-region-selection">
                <select name="<?php echo $this->search->getRegionSelectionElementName($scope) ?>">
                    <?php
                    foreach($this->countries as $country)
                    {
                        ?>
                            <option <?php echo ($persistedRegion == $country['name']) ? 'selected' : ''; ?> value="<?php echo $country['name'] ?>"><?php echo $country['label'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        <?php
    }

    /**
     * @var UI_Page_Navigation_Item_Search
     */
    protected $search;

    /**
     * @var array<int,array<string,string>>
     */
    protected $scopes;

    /**
     * @var string
     */
    protected $scopeID;

    /**
     * @var array<int,array<string,string>>
     */
    protected $countries;

    protected function preRender() : void
    {
        $this->search = $this->getObjectVar('search', UI_Page_Navigation_Item_Search::class);
        $this->scopes = $this->search->getScopes();
        $this->countries = $this->search->getRegions();
        $this->scopeID = $this->getStringVar('scope_id');

        $this->ui->addStylesheet('ui-nav-search.css');
    }
}
