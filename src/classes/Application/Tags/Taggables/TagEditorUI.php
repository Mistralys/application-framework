<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\AppFactory;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\OutputBuffering;
use UI;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

class TagEditorUI implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const STYLESHEET_FILE = 'ui/tags/tag-editor.css';
    public const JAVASCRIPT_FILE_MANAGER = 'ui/tags/tag-editor-manager.js';
    public const JAVASCRIPT_FILE_EDITOR = 'ui/tags/tag-editor.js';

    private Taggable $taggable;
    private static bool $jsInjected = false;

    public function __construct(Taggable $taggable)
    {
        $this->taggable = $taggable;
    }

    private static function injectJS() : void
    {
        if (self::$jsInjected) {
            return;
        }

        self::$jsInjected = true;

        $ui = UI::getInstance();
        $ui->addStylesheet(self::STYLESHEET_FILE);
        $ui->addJavascript(self::JAVASCRIPT_FILE_MANAGER);
        $ui->addJavascript(self::JAVASCRIPT_FILE_EDITOR);

        AppFactory::createTags()->injectJS();

        $objectName = 'TE'.nextJSID();

        $ui->addJavascriptHead(sprintf(
            "const %s = new TagEditorManager()",
            $objectName
        ));

        $ui->addJavascriptOnload(sprintf(
            "%s.Start()",
            $objectName
        ));
    }

    public function render() : string
    {
        self::injectJS();

        OutputBuffering::start();

        ?>
        <div class="tag-editor" data-primary="<?php echo $this->taggable->getUniqueID() ?>">
            <div class="tag-editor-list">
                <?php echo UI::icon()->tags() ?>
                <span><?php echo implode('</span> <span>', $this->taggable->getLabels()) ?></span>
            </div>
            <div class="tag-editor-ui" style="display: none"></div>
        </div>
        <?php

        return OutputBuffering::get();
    }
}