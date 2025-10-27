<?php
/**
 * @package UI
 * @subpackage Navigation
 */

declare(strict_types=1);

namespace UI\Page\Navigation;

use Application\Themes\DefaultTemplate\UI\Nav\TextLinksNavigationTmpl;
use UI_Page;
use UI_Page_Navigation;

/**
 * A navigation bar that displays text links.
 *
 * @package UI
 * @subpackage Navigation
 *
 * @see TextLinksNavigationTmpl
 */
class TextLinksNavigation extends UI_Page_Navigation
{
    public function __construct(UI_Page $page, string $id)
    {
        parent::__construct($page, $id);

        $this->setTemplateID(TextLinksNavigationTmpl::class);
    }

    /**
     * @return class-string<TextLinksNavigationTmpl>
     */
    public function getTemplateID(): string
    {
        return TextLinksNavigationTmpl::class;
    }
}
