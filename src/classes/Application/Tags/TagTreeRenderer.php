<?php

declare(strict_types=1);

namespace Application\Tags;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OutputBuffering;
use AppUtils\Traits\OptionableTrait;
use UI_Renderable;

class TagTreeRenderer extends UI_Renderable
    implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_SHOW_ROOT = 'showRoot';
    public const OPTION_EDITABLE = 'editable';

    private TagRecord $rootTag;

    public function __construct(TagRecord $rootTag)
    {
        $this->rootTag = $rootTag;

        parent::__construct(null);
    }

    protected function _render() : string
    {
        OutputBuffering::start();

        ?>
        <ul class="tags-tree">
            <?php
            if($this->isRootShown()) {
                $this->renderTree($this->rootTag);
            } else {
                $tags = $this->rootTag->getSubTags();
                foreach ($tags as $tag) {
                    $this->renderTree($tag);
                }
            }
            ?>
        </ul>
        <?php

        return OutputBuffering::get();
    }

    private function renderTree(TagRecord $record) : void
    {
        ?>
        <li class="tag-entry">
            <?php
            echo $record->getLabelLinked();

            $subTags = $record->getSubTags();
            if(!empty($subTags))
            {
                ?>
                <ul class="tags-tree">
                    <?php
                    foreach ($subTags as $subTag) {
                        $this->renderTree($subTag);
                    }
                    ?>
                </ul>
                <?php
            }

            if($this->isEditable()) {
                ?>
                <div class="tag-entry-actions">
                    <a href="<?php echo $record->getAdminCreateSubTagURL() ?>"><?php pt('Add subtag') ?></a>
                </div>
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
