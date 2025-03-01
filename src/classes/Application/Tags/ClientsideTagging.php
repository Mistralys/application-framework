<?php

declare(strict_types=1);

namespace Application\Tags;

use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggingDialog;
use UI;

class ClientsideTagging
{
    public const SCRIPT_TAGGING_MANAGER = 'tagging/manager.js';
    public const SCRIPT_TAGGING_DIALOG = 'tagging/dialog.js';

    private string $objectID;
    private UI $ui;
    private bool $injected = false;

    public function __construct(UI $ui)
    {
        $this->ui = $ui;
        $this->objectID = 'tagging'.nextJSID();
    }

    public function getObjectID() : string
    {
        $this->inject();

        return $this->objectID;
    }

    public function inject() : void
    {
        if($this->injected) {
            return;
        }

        $this->injected = true;

        $this->ui->addJavascript(self::SCRIPT_TAGGING_MANAGER);
        $this->ui->addJavascript(self::SCRIPT_TAGGING_DIALOG);
        $this->ui->addJavascriptHeadVariable($this->objectID, 'new TaggingManager()');
    }

    public function createDialog(TaggableInterface $taggable) : TaggingDialog
    {
        return new TaggingDialog($taggable);
    }
}
