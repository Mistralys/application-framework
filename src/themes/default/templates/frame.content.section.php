<?php
/**
 * File containing the template class {@see template_default_frame_content_section}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_content_section
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;

/**
 * Main template for the frame skeleton of all pages.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page_Section
 */
class template_default_frame_content_section extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        ?>
            <section <?php echo compileAttributes($this->resolveSectionAttributes()) ?>>
                <?php
                    $this->displayAnchor();
                    $this->displayToolbar();
                    $this->displayTitleBar();
                    $this->displayBody();
                ?>
            </section>
        <?php
    }

    protected function resolveSectionAttributes() : array
    {
        $classes = array();

        $classes[] = 'section';
        $classes[] = 'group-'.$this->section->getGroup();

        if($this->section->isCollapsible()) {
            $classes[] = 'collapsible';
        }

        if($this->section->hasAbstract()) {
            $classes[] = 'with-abstract';
        }

        if($this->section->hasContextButtons()) {
            $classes[] = 'with-context-buttons';
        }

        if($this->section->isCompact()) {
            $classes[] = 'compact';
        }

        $style = $this->section->getVisualStyle();
        if($style !== null) {
            $classes[] = $style;
        }

        $classes = array_merge($classes, $this->section->getClasses());

        return array(
            'id' => $this->section->getID(),
            'class' => implode(' ', $classes)
        );
    }

    protected function displayAnchor() : void
    {
        $anchor = $this->resolveAnchor();

        if(empty($anchor)) {
            return;
        }

        ?>
            <a id="<?php echo $anchor ?>" name="<?php echo $anchor ?>"></a>
        <?php
    }

    protected function resolveAnchor() : string
    {
        $anchor = $this->section->getAnchor();

        if (!empty($anchor)) {
            return $anchor;
        }

        $title = $this->section->getTitle();
        if(!empty($title)) {
            return ConvertHelper::transliterate(strip_tags($title));
        }

        return '';
    }

    protected function displayBody() : void
    {
        ?>
            <div <?php echo compileAttributes($this->resolveBodyAttributes()) ?>>
                <div <?php echo compileAttributes($this->resolveWrapperAttributes()) ?>>
                    <?php
                        $this->displayAbstract();
                        echo $this->getStringVar('content');
                    ?>
                </div>
            </div>
        <?php
    }

    protected function displayAbstract() : void
    {
        if (!$this->section->hasAbstract()) {
            return;
        }

        ?>
            <p class="abstract">
                <?php echo $this->section->getAbstract() ?>
            </p>
        <?php
    }

    protected function resolveWrapperAttributes() : array
    {
        $attributes = array();
        $classes = array('body-wrapper');

        $maxBodyHeight = $this->section->getMaxBodyHeight();

        if ($maxBodyHeight !== null) {
            $classes[] = 'max-height';
            $attributes['style'] = 'max-height:' . $maxBodyHeight->toCSS();
        }

        $attributes['class'] = implode(' ', $classes);

        return $attributes;
    }

    protected function resolveBodyAttributes() : array
    {
        $attributes = array();
        $classes = array();

        $classes[] = 'section-body';
        $classes[] = $this->section->getType() . '-body';

        if ($this->section->isCollapsible()) {
            $classes[] = 'collapse';
            if (!$this->section->isCollapsed()) {
                $classes[] = 'in';
            }
        }

        $attributes['class'] = implode(' ', $classes);
        $attributes['id'] = $this->section->getID() . '-body';

        return $attributes;
    }

    protected function displayTitleBar() : void
    {
        $title = $this->resolveTitleText();

        if(empty($title)) {
            return;
        }

        ?>
            <h3 <?php echo compileAttributes($this->resolveHeaderAttributes()) ?>>
                <?php $this->displayStatusElements() ?>
                <?php echo $title ?>
                <?php $this->displayTitleTagline() ?>
            </h3>
        <?php
    }

    protected function displayStatusElements() : void
    {
        if(!$this->section->hasStatusElements())
        {
            return;
        }

        $elements = $this->section->getStatusElements();

        ?>
        <div class="section-status-elements">
            <?php
                foreach($elements as $element)
                {
                    ?>
                    <div class="section-status-element">
                        <?php echo $element ?>
                    </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }

    /**
     * @return array<string,string>
     */
    protected function resolveHeaderAttributes() : array
    {
        $attributes = array();
        $headerClasses = array('section-header', $this->type . '-header');

        if ($this->section->hasTagline()) {
            $headerClasses[] = 'with-tagline';
        } else {
            $headerClasses[] = 'without-tagline';
        }

        if ($this->section->isCollapsible())
        {
            $id = $this->section->getID();

            $headerClasses[] = 'collapsible';
            $attributes['id'] = $id . '-header';
            $attributes['data-toggle'] = 'collapse';
            $attributes['data-target'] = '#' . $id . '-body';


            if ($this->section->isCollapsed()) {
                $headerClasses[] = 'collapsed';
            }
        }
        else
        {
            $headerClasses[] = 'regular';
        }

        $attributes['class'] = implode(' ', $headerClasses);

        return $attributes;
    }

    protected function resolveTitleText() : string
    {
        $title = $this->section->getTitle();

        if(empty($title) && $this->isToolbarEnabled()) {
            $title = '&nbsp;';
        }

        $result = sb()->add((string)$this->section->getIcon());

        $result->add($title);

        if($this->section->isCollapsible())
        {
            $id = $this->section->getID();

            $result
                ->icon(UI::icon()->caretDown()
                    ->addClass('toggle')
                    ->addClass('toggle-expand')
                    ->setHidden($this->section->isExpanded())
                    ->setID($id.'-expand')
                )
                ->icon(UI::icon()->caretUp()
                    ->addClass('toggle')
                    ->addClass('toggle-collapse')
                    ->setHidden($this->section->isCollapsed())
                    ->setID($id.'-collapse')
                );
        }

        return $result->render();
    }

    protected function displayTitleTagline() : void
    {
        if(!$this->section->hasTagline()) {
            return;
        }

        ?>
            <small class="section-tagline <?php echo $this->section->getType() ?>-tagline">
                <?php echo $this->section->getTagline() ?>
            </small>
        <?php
    }

    public function isToolbarEnabled() : bool
    {
        return $this->section->hasQuickSelector() || $this->section->hasContextButtons();
    }

    protected function displayToolbar() : void
    {
        if(!$this->isToolbarEnabled()) {
            return;
        }

        ?>
            <div class="btn-toolbar pull-right section-toolbar">
                <?php
                    $this->displayQuickSelector();
                    $this->displayContextButtons();
                ?>
            </div>
        <?php
    }

    protected function displayContextButtons() : void
    {
        if (!$this->section->hasContextButtons()) {
            return;
        }

        $buttons = $this->section->getContextButtons();

        // Ensure they are all the same size
        foreach ($buttons as $contextButton) {
            $this->configureButton($contextButton);
        }

        ?>
            <div class="btn-group section-context-buttons">
                <?php echo implode('', $buttons) ?>
            </div>
        <?php
    }

    /**
     * @param UI_Button|UI_Bootstrap_ButtonDropdown $button
     */
    protected function configureButton($button) : void
    {
        $button->makeSmall();
    }

    protected function displayQuickSelector() : void
    {
        $quick = $this->section->getQuickSelector();

        if ($quick === null) {
            return;
        }

        $quick->makeCompact();

        ?>
            <div class="btn-group">
                <?php $quick->display(); ?>
            </div>
        <?php
    }

    // region: Setup

    /**
     * @var UI_Page_Section
     */
    private $section;

    /**
     * @var string
     */
    private $type;

    protected function preRender() : void
    {
        $this->section = $this->getObjectVar('section', UI_Page_Section::class);
        $this->type = $this->section->getType();

        $this->ui->addJavascript('ui/section.js');

        $this->ui->addJavascriptHeadStatement(
            sprintf('var %s = UI.RegisterSection', 'SC' . $this->section->getID()),
            $this->section->getID(),
            $this->section->getType(),
            $this->section->isCollapsible(),
            $this->section->isCollapsed(),
            $this->section->getGroup()
        );

    }

    // endregion
}
