<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\AppFactory;
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
    public const JAVASCRIPT_FILE_TAGGABLE = 'ui/tags/taggable.js';
    public const JAVASCRIPT_FILE_TAGGABLE_TAG = 'ui/tags/taggable-tag.js';

    public const JAVASCRIPT_FILES = array(
        self::JAVASCRIPT_FILE_MANAGER,
        self::JAVASCRIPT_FILE_EDITOR,
        self::JAVASCRIPT_FILE_TAGGABLE,
        self::JAVASCRIPT_FILE_TAGGABLE_TAG
    );
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

        foreach(self::JAVASCRIPT_FILES as $file) {
            $ui->addJavascript($file);
        }

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

        // --------------------------------------------
        //          /!\    !WARNING!    /!\
        // --------------------------------------------
        // Changes to the HTML structure of the tag editor
        // must be mirrored in the JavaScript code.

        ?>
        <div class="tag-editor" data-primary="<?php echo $this->taggable->getUniqueID() ?>">
            <div class="tag-editor-list">
                <span class="tag-editor-list-icon"><?php echo UI::icon()->tags() ?></span>
                <span class="tag-editor-list-tags">
                    <span><?php echo implode('</span> <span>', $this->taggable->getLabels()) ?></span>
                </span>
            </div>
            <div class="tag-editor-ui" style="display: none"></div>
        </div>
        <?php

        return OutputBuffering::get();
    }
}
