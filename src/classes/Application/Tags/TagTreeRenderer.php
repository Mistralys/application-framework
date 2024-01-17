<?php

declare(strict_types=1);

namespace Application\Tags;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OutputBuffering;
use AppUtils\Traits\OptionableTrait;
use UI;
use UI_Renderable;

class TagTreeRenderer extends UI_Renderable
    implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_SHOW_ROOT = 'showRoot';
    public const OPTION_EDITABLE = 'editable';

    private TagRecord $rootTag;
    private ?TagRecord $activeTag = null;

    public function __construct(TagRecord $rootTag)
    {
        $this->rootTag = $rootTag;

        parent::__construct(null);
    }

    public function setActiveTag(TagRecord $record) : self
    {
        $this->activeTag = $record;
        return $this;
    }

    protected function _render() : string
    {
        $this->ui->addStylesheet('ui/tags.css');

        OutputBuffering::start();

        $tags = $this->rootTag->getSubTags();

        ?>
        <div class="tags-tree-wrapper">
            <ul class="tags-tree last-with-spacing <?php if(!empty($tags)) { echo 'with-subtags'; } ?>">
                <?php
                if($this->isRootShown()) {
                    $this->renderTree($this->rootTag);
                } else {
                    foreach ($tags as $tag) {
                        $this->renderTree($tag);
                    }
                }
                ?>
            </ul>
        </div>
        <?php

        return OutputBuffering::get();
    }

    private function renderTree(TagRecord $record) : void
    {
        $subTags = $record->getSubTags();

        $classes = array('tag-entry');
        if($record === $this->activeTag) { $classes[] = 'active'; }
        if($record->isRootTag()) {$classes[] = 'root';}

        ?>
        <li class="<?php echo implode(' ', $classes) ?>">
            <div class="tag-label">
                <a href="<?php echo $record->getAdminTagTreeURL() ?>">
                    <?php
                    echo sb()
                        ->icon(UI::icon()->tags())
                        ->add($record->getLabelLinked());
                    ?>
                </a>
                <?php
                if($this->isEditable()) {
                    ?>
                    <div class="tag-entry-actions">
                        <?php
                        echo $this->ui->createButtonGroup()
                            ->makeMini()
                            ->addButton(UI::button()
                                ->setIcon(UI::icon()->add())
                                ->setTooltip(t('Add a sub-tag to %1$s', $record->getLabel()))
                                ->link($record->getAdminCreateSubTagURL())
                            )
                            ->addButton(UI::button()
                                ->setIcon(UI::icon()->delete())
                                ->makeDangerous()
                                ->makeConfirm(t('Are you sure you want to delete %1$s and all its sub-tags?', sb()->bold($record->getLabel())))
                                ->setTooltip(t('Delete this tag and all its sub-tags.'))
                                ->link($record->getAdminDeleteURL())
                                ->requireFalse($record === $this->rootTag)
                            );
                        ?>
                    </div>
                <?php
            }

            ?>
            </div>
            <?php

            if(!empty($subTags))
            {
                ?>
                <ul class="tags-tree last-with-spacing with-subtags">
                    <?php
                    foreach ($subTags as $subTag) {
                        $this->renderTree($subTag);
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </li>
        <?php
    }

    /**
     * @param bool $showRoot
     * @return $this
     */
    public function setShowRoot(bool $showRoot) : self
    {
        return $this->setOption(self::OPTION_SHOW_ROOT, $showRoot);
    }

    public function isRootShown() : bool
    {
        return $this->getBoolOption(self::OPTION_SHOW_ROOT);
    }

    public function makeEditable() : self
    {
        return $this->setEditable(true);
    }

    public function setEditable(bool $editable) : self
    {
        return $this->setOption(self::OPTION_EDITABLE, $editable);
    }

    public function isEditable() : bool
    {
        return $this->getBoolOption(self::OPTION_EDITABLE);
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_SHOW_ROOT => true,
            self::OPTION_EDITABLE => false
        );
    }
}
