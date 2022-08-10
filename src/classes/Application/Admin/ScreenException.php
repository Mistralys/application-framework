<?php
/**
 * @package Application
 * @subpackage Admin
 * @see \Application\Admin\ScreenException
 */

declare(strict_types=1);

namespace Application\Admin;

use Application_Admin_Exception;
use Application_Admin_ScreenInterface;
use Throwable;

/**
 * Specialized admin screen exception, which automatically
 * adds screen-related information to the exception's developer
 * details.
 *
 * @package Application
 * @subpackage Exceptions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ScreenException extends Application_Admin_Exception
{
    private Application_Admin_ScreenInterface $screen;

    public function __construct(Application_Admin_ScreenInterface $screen, string $message, string $developerInfo = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->screen = $screen;

        $developerInfo = $this->collectInfo().$developerInfo;

        parent::__construct($message, $developerInfo, $code, $previous);
    }

    public function getScreen() : Application_Admin_ScreenInterface
    {
        return $this->screen;
    }

    private function collectInfo() : string
    {
        return (string)sb()
            ->add('Screen class:')
            ->add(get_class($this->screen))
            ->eol()
            ->add('URL path:')
            ->add($this->screen->getURLPath());
    }
}
