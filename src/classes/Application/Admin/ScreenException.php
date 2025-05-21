<?php
/**
 * @package Application
 * @subpackage Admin
 * @see \Application\Admin\ScreenException
 */

declare(strict_types=1);

namespace Application\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Admin_Exception;
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
    private AdminScreenInterface $screen;

    public function __construct(AdminScreenInterface $screen, string $message, string $developerInfo = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->screen = $screen;

        $developerInfo = $this->collectInfo().$developerInfo;

        parent::__construct($message, $developerInfo, $code, $previous);
    }

    public function getScreen() : AdminScreenInterface
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
