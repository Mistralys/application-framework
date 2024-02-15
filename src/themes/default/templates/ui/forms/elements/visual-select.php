<?php

declare(strict_types=1);

use UI\ClientResourceCollection;
use UI\Form\Element\VisualSelect\ImageSet;
use UI\Form\Element\VisualSelect\VisualSelectOption;

class template_default_ui_forms_elements_visual_select extends UI_Page_Template_Custom
{
    public static function injectJavascript(ClientResourceCollection $ui) : void
    {
        $ui->addJavascript('forms/visual-select/element.js');
        $ui->addJavascript('forms/visual-select/item.js');
        $ui->addStylesheet('forms/visualselect.css');
    }

    protected function generateOutput(): void
    {
        self::injectJavascript($this->getUI()->createResourceCollection());

        $this->element->addClass('select-visualselect');
        $this->element->addContainerClass('visel-images');

        $id = $this->element->getAttribute('id');
        if (empty($id)) {
            $id = 'visel' . nextJSID();
            $this->element->setAttribute('id', $id);
        }

        $filteringEnabled = $this->element->isFilteringEnabled();
        $groupingEnabled = $this->element->isGroupingEnabled();

        if($filteringEnabled) {
            $this->element->addContainerClass('filtering-enabled');
        }

        echo $this->getStringVar('html');

        if($this->element->isFrozen()) {
            return;
        }

        $varName = 'viselObj'.nextJSID();
        $this->ui->addJavascriptOnload(sprintf(
            "var %s = new VisualSelectElement('%s')",
            $varName,
            $id
        ));

        $options = $this->element->getOptionContainer()->getOptions();
        $sets = $this->element->getImageSets();

        ?>
        <div class="<?php echo implode(' ', $this->element->getContainerClasses()) ?>" id="<?php echo $id ?>-visel">
            <div class="visel-toolbar">
                <?php
                if(!empty($sets))
                {
                    $group = UI::getInstance()->createButtonGroup();
                    foreach($sets as $set)
                    {
                        $group->addButton(
                            UI::button($set->getLabel())
                                ->setAttribute(ImageSet::ATTRIBUTE_SET_ID, $set->getID())
                                ->addClass('visel-btn-switch-set')
                        );
                    }

                    echo $group;
                }

                if($groupingEnabled)
                {
                    $group = UI::getInstance()->createButtonGroup();
                    $group->addButton(
                        UI::button(t('Flat view'))
                            ->setIcon(UI::icon()->flat())
                            ->addClass('visel-btn-flat-view')
                    );
                    $group->addButton(
                        UI::button(t('Grouped view'))
                            ->setIcon(UI::icon()->grouped())
                            ->addClass('visel-btn-grouped-view')
                    );

                    echo $group;
                }
                ?>
            </div>
            <?php
            if($filteringEnabled)
            {
                ?>
                <div class="visel-filter-widget">
                    <input type="text" value="" class="search-query visel-filter-input" placeholder="<?php pt('Filter the list...') ?>">
                    <?php

                    echo UI::button()
                        ->makeMini()
                        ->setIcon(UI::icon()->delete())
                        ->setTooltip(t('Clear the filter'))
                        ->addClass('visel-btn-clear-filter');
                    ?>
                </div>
                <?php
            }
            ?>
            <div class="visel-body">
                <?php
                if($groupingEnabled)
                {
                    ?>
                    <ul class="visel-items grouped">
                        <?php
                        $this->renderGroupsJumpMenu($this->getRootGroups($options));
                        $this->renderOptionsList($options);
                        ?>
                    </ul>
                    <?php
                }
                ?>
                <ul class="visel-items flat">
                    <?php
                    $this->renderOptionsList($this->element->getOptionsFlat());
                    ?>
                </ul>
            </div>
            <div class="visel-expand">
                <?php echo UI::icon()->expand().' '.t('Expand/Collapse') ?>
            </div>
        </div>
        <?php

        if($this->activeSet !== null)
        {
            $this->ui->addJavascriptOnload(sprintf(
                "%s.SwitchSet('%s')",
                $varName,
                $this->activeSet->getID()
            ));
        }
    }

    /**
     * @param array<int,HTML_QuickForm2_Element_VisualSelect_Optgroup|HTML_QuickForm2_Element_Select_Optgroup|VisualSelectOption> $options
     * @param HTML_QuickForm2_Element_VisualSelect_Optgroup|null $group
     * @return void
     * @throws UI_Exception
     */
    protected function renderOptionsList(array $options, ?HTML_QuickForm2_Element_VisualSelect_Optgroup $group=null) : void
    {
        foreach($options as $option)
        {
            if($option instanceof HTML_QuickForm2_Element_VisualSelect_Optgroup)
            {
                $this->renderOptionGroup($option);
            }
            else if($option instanceof VisualSelectOption)
            {
                if(!$option->hasImage()) {
                    continue;
                }

                $this->renderOption($option, $group);
            }
        }
    }

    private function renderOptionGroup(HTML_QuickForm2_Element_VisualSelect_Optgroup $group) : void
    {
        $setID = $group->getImageSetID();

        ?>
        <li class="visel-group" <?php if(!empty($setID)) { echo ImageSet::ATTRIBUTE_SET_ID.'="'.$setID.'"'; } ?>>
            <h4 id="<?php echo $group->getElementID() ?>" class="visel-group-header">
                <?php echo $group->getLabel() ?>
            </h4>
            <ul class="visel-items">
                <?php
                $this->renderOptionsList($group->getOptions(), $group);
                ?>
            </ul>
        </li>
        <?php
    }

    /**
     * @param VisualSelectOption $option
     * @param HTML_QuickForm2_Element_VisualSelect_Optgroup|null $group
     * @return void
     * @throws UI_Exception
     */
    private function renderOption(VisualSelectOption $option, ?HTML_QuickForm2_Element_VisualSelect_Optgroup $group=null) : void
    {
        $imgAtts = array(
            'id' => nextJSID(),
            'title' => $option->getLabel(),
            'alt' => $option->getLabel(),
            'src' => $option->getImageURL(),
            'class' => 'visel-item-image',
            'style' => 'width:'.$this->element->getThumbnailSize().'px'
        );

        JSHelper::tooltipify($imgAtts['id']);

        $class = 'visel-item';
        $value = $option->getValue();

        if($value === '') {
            $class .= ' no-icon';
        }

        if($this->element->isCheckered()) {
            $class .= ' checkered';
        }

        $liAtts = array(
            'class' => $class,
            'data-value' => $value
        );

        if($group !== null) {
            $liAtts['data-group'] = $group->getLabel();
        }

        if($option->hasImageSet()) {
            $liAtts[ImageSet::ATTRIBUTE_SET_ID] = $option->getImageSetID();
        }

        ?>
        <li <?php echo compileAttributes($liAtts) ?>>
            <img <?php echo compileAttributes($imgAtts) ?>/>
        </li>
        <?php
    }

    /**
     * @param array<int,HTML_QuickForm2_Element_VisualSelect_Optgroup|array<string,string>> $groups
     * @return void
     */
    protected function renderGroupsJumpMenu(array $groups) : void
    {
        if(empty($groups)) {
            return;
        }

        ?>
        <?php pts('Jump to group:') ?>
        <ul class="visel-groups-menu unstyled">
            <?php
                foreach ($groups as $group)
                {
                    $liAtts = array(
                        ImageSet::ATTRIBUTE_SET_ID => $group->getImageSetID()
                    );

                    ?>
                    <li <?php echo  compileAttributes($liAtts) ?>>
                        <a href="#<?php echo $group->getElementID() ?>">
                            <?php echo $group->getLabel() ?>
                        </a>
                    </li>
                    <?php
                }
            ?>
        </ul>
        <?php
    }

    /**
     * @param array<int,HTML_QuickForm2_Element_VisualSelect_Optgroup|HTML_QuickForm2_Element_Select_Optgroup|array<string,string>> $options
     * @return HTML_QuickForm2_Element_VisualSelect_Optgroup[]
     */
    protected function getRootGroups(array $options) : array
    {
        $groups = array();

        foreach ($options as $option)
        {
            if ($option instanceof HTML_QuickForm2_Element_VisualSelect_Optgroup)
            {
                $groups[] = $option;
            }
        }

        return $groups;
    }

    protected HTML_QuickForm2_Element_VisualSelect $element;
    protected ?ImageSet $activeSet = null;
    protected ?string $activeSetID = null;

    protected function preRender(): void
    {
        $this->element = $this->getObjectVar('element', HTML_QuickForm2_Element_VisualSelect::class);
        $this->activeSet = $this->element->getActiveImageSet();

        if(isset($this->activeSet)) {
            $this->activeSetID = $this->activeSet->getID();
        }
    }
}
