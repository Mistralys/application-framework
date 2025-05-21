<?php
/**
 * @package User Interface
 * @subpackage Admin URLs
 */

declare(strict_types=1);

namespace UI\AdminURLs;

use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Interfaces\RenderableInterface;
use AppUtils\URLBuilder\URLBuilderInterface;

/**
 * Interface for admin URL instances.
 * See {@see AdminURL} for the implementation.
 *
 * @package User Interface
 * @subpackage Admin URLs
 * @see AdminURL
 */
interface AdminURLInterface extends URLBuilderInterface
{
    /**
     * Adds an admin area screen parameter.
     * @param string $name
     * @return $this
     */
    public function area(string $name) : self;

    /**
     * Adds an admin mode screen parameter.
     * @param string $name
     * @return $this
     */
    public function mode(string $name) : self;

    /**
     * Adds an admin submode screen parameter.
     * @param string $name
     * @return $this
     */
    public function submode(string $name) : self;

    /**
     * Adds an admin action screen parameter.
     * @param string $name
     * @return $this
     */
    public function action(string $name) : self;

    /**
     * Add the parameter to enable the application simulation mode.
     * @param bool $enabled
     * @return $this
     */
    public function simulation(bool $enabled=true) : self;
}
