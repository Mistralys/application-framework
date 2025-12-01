<?php
/**
 * @package AppFrameworkTestClasses
 * @subpackage Admin
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\Admin;

use Application\Admin\Area\BaseMode;
use Application\Interfaces\Allowable\DeveloperAllowedInterface;
use Application\Traits\Allowable\DeveloperAllowedTrait;

/**
 * A stub admin screen for testing purposes that requires developer rights.
 *
 * @package AppFrameworkTestClasses
 * @subpackage Admin
 */
final class DeveloperScreenStub extends BaseMode implements DeveloperAllowedInterface
{
    use DeveloperAllowedTrait;

    public function getURLName(): string
    {
        return 'developer-screen-stub';
    }

    public function getNavigationTitle(): string
    {
        return 'Developer Screen Stub';
    }

    public function getTitle(): string
    {
        return 'Developer Screen Stub Title';
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }
}
