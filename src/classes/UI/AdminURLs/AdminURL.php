<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use Application\Application;
use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\URLBuilder\URLBuilder;
use AppUtils\URLInfo;

/**
 * Helper class used to build admin screen URLs.
 *
 * To create an instance, use the {@see \UI::adminURL()} method,
 * or the {@see AdminURL::create()} method.
 *
 * @package User Interface
 * @subpackage Admin URLs
 * @see AdminURLInterface
 *
 * @method static AdminURL create(array $params = array())
 * @method static AdminURL createFromURL(string $url)
 * @method static AdminURL createFromURLInfo(URLInfo $info)
 */
class AdminURL extends URLBuilder implements AdminURLInterface
{
    /**
     * Adds an admin area screen parameter.
     * @param string $name
     * @return $this
     */
    public function area(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_PAGE, $name);
    }

    /**
     * Adds an admin mode screen parameter.
     * @param string $name
     * @return $this
     */
    public function mode(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_MODE, $name);
    }

    /**
     * Adds an admin submode screen parameter.
     * @param string $name
     * @return $this
     */
    public function submode(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_SUBMODE, $name);
    }

    /**
     * Adds an admin action screen parameter.
     * @param string $name
     * @return $this
     */
    public function action(string $name) : self
    {
        return $this->string(AdminScreenInterface::REQUEST_PARAM_ACTION, $name);
    }

    /**
     * Add the parameter to enable the application simulation mode.
     * @param bool $enabled
     * @return $this
     */
    public function simulation(bool $enabled=true) : self
    {
        return $this->bool(Application::REQUEST_VAR_SIMULATION, $enabled, true);
    }
}
