<?php

declare(strict_types=1);

namespace UI\Bootstrap\Dropdown;

use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use UI;

class AJAXLoader
{
    private string $methodName;
    private ?ArrayDataCollection $payload;
    private string $menuID;
    private string $objID;
    private UI $ui;
    private bool $initialized = false;

    public function __construct(UI $ui, string $menuID, string $methodName, ?ArrayDataCollection $payload = null)
    {
        $this->ui = $ui;
        $this->menuID = $menuID;
        $this->methodName = $methodName;
        $this->payload = $payload;
        $this->objID = 'ad'.nextJSID();
    }

    /**
     * Renders the placeholder for the menu.
     * @return string
     */
    public function renderPlaceholder() : string
    {
        $this->initClientAJAX();

        return sprintf(
            '<div id="%s-ajax-body"></div>',
            $this->menuID
        );
    }

    protected function initClientAJAX() : void
    {
        if($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->ui->addJavascript('ui/ajax-dropdown.js');

        $this->ui->addJavascriptHead(sprintf(
            "const %s = new AJAXDropdown('%s', '%s', %s);",
            $this->objID,
            $this->menuID,
            $this->methodName,
            $this->data2js()
        ));

        $this->ui->addJavascriptOnload(sprintf('%s.Start()', $this->objID));
    }

    protected function data2js() : string
    {
        if(isset($this->payload)) {
            return JSONConverter::var2json($this->payload->getData());
        }

        return 'null';
    }
}
