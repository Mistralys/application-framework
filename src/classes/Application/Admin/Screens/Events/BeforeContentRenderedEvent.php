<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use AppUtils\Interfaces\StringableInterface;
use TestDriver\Area\TestingScreen\ReplaceContentScreen;
use UI_Exception;
use UI_Themes_Theme_ContentRenderer;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeContentRendered()
 * @see \Application_Traits_Admin_Screen::renderContent()
 */
class BeforeContentRenderedEvent extends BaseScreenEvent
{
    public const EVENT_NAME = 'BeforeContentRendered';

    private ?string $content = null;

    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this->getScreen()->getRenderer();
    }

    /**
     * Replaces the screen's content with the provided content.
     *
     * **WARNING:** Handle with care. If the screen has sub-screens,
     * this will effectively hide them. Sub-screen rendering is ignored
     * if the screen itself has non-empty content.
     *
     * Example: See {@see ReplaceContentScreen}
     *
     * @param string|number|StringableInterface $content
     * @return $this
     * @throws UI_Exception
     */
    public function replaceScreenContentWith($content) : self
    {
        $content = toString($content);
        if(!empty($content)) {
            $this->content = $content;
        }

        return $this;
    }

    /**
     * Whether this event defines content to replace the
     * screen's content with.
     *
     * @return bool
     */
    public function replacesContent() : bool
    {
        return $this->content !== null;
    }

    public function getContent() : string
    {
        return (string)$this->content;
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
