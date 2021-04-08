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
    protected function generateOutput(): void
    {
        ?>
        <form method="post" class="nav-search <?php echo implode(' ', $this->search->getClasses()) ?>">
            <?php $this->renderHiddens() ?>
            <div class="search-inputs">
                <input name="<?php echo $this->search->getSearchElementName() ?>"
                       type="text" class="search-input search-input-terms"
                       placeholder="<?php pt('Search...') ?>"
                       value="<?php echo htmlspecialchars($this->search->getSearchTerms(), ENT_QUOTES, 'UTF-8') ?>"/>
                <?php $this->renderScopes() ?>
                <?php $this->renderCountrySelection() ?>
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

    protected function renderScopes(): void
    {
        if (empty($this->scopes)) {
            return;
        }

        $selectedScopeID = $this->search->getSelectedScopeID();

        ?>
        <select name="<?php echo $this->search->getScopeElementName() ?>" class="search-input search-input-scope">
            <?php
            foreach ($this->scopes as $scope) {
                $selected = '';
                if ($selectedScopeID == $scope['name']) {
                    $selected = 'selected';
                }

                ?>
                <option <?php echo $selected ?> value="<?php echo $scope['name'] ?>">
                    <?php echo $scope['label'] ?>
                </option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    protected function renderHiddens(): void
    {
        $hiddens = $this->search->getHiddenVars();

        ?>
        <div class="form-hiddens" style="display:none">
            <?php
            foreach ($hiddens as $name => $value) {
                ?>
                <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>"/>
                <?php
            }
            ?>
        </div>
        <?php
    }

    protected function renderCountrySelection(string $scopeID = '')
    {
        if (!$this->search->hasCountries()) {
            return;
        }

        $selectedCountry = $this->search->getSelectedCountryID($scopeID);

        ?>
        <select class="search-country-selection"
                name="<?php echo $this->search->getCountrySelectionElementName($scopeID) ?>">
            <option value="any"> <?php pt('Any country') ?> </option>

            <?php
            foreach ($this->countries as $country) {
                $selected = '';
                if ($selectedCountry == $country['name']) {
                    $selected = 'selected';
                }

                ?>
                <option <?php echo $selected ?> value="<?php echo $country['name'] ?>">
                    <?php echo $country['label'] ?>
                </option>
                <?php
            }
            ?>
        </select>
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
     * @var array<int,array<string,string>>
     */
    protected $countries;

    protected function preRender(): void
    {
        $this->search = $this->getObjectVar('search', UI_Page_Navigation_Item_Search::class);
        $this->scopes = $this->search->getScopes();
        $this->countries = $this->search->getCountries();

        $this->ui->addStylesheet('ui-nav-search.css');
    }
}
