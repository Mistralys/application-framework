<?php
/**
 * File containing the {@link Application_Environments_Environment_Requirement_BoolTrue} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment_Requirement_BoolTrue
 */

declare(strict_types=1);

/**
 * Requires the environment to be running on a host
 * containing a specific search string in its host name.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments_Environment_Requirement_BoolTrue extends Application_Environments_Environment_Requirement
{
    /**
     * @var bool
     */
    protected $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function isValid() : bool
    {
        return $this->value === true;
    }
}
