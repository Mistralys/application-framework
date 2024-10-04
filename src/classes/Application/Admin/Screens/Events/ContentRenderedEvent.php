<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onContentRendered()
 * @see \Application_Traits_Admin_Screen::renderContent()
 */

class ContentRenderedEvent extends BaseScreenEvent
{
    public const EVENT_NAME = 'ContentRendered';

    /**
     * Whether the screen rendered any content.
     *
     * NOTE: If no content has been rendered, the rendering
     * is passed on to the sub-screens, if any.
     *
     * @return bool
     */
    public function hasRenderedContent() : bool
    {
        return $this->getArgumentBool(1);
    }

    public function isCancellable(): bool
    {
        return false;
    }
}
