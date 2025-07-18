<?php
/**
 * @package User Interface
 * @subpackage List Builder
 */

declare(strict_types=1);

namespace UI\DataGrid\ListBuilder;

use Application\Interfaces\Admin\AdminScreenInterface;
use UI\Interfaces\ListBuilderInterface;


/**
 * Interface for admin screens that use a {@see ListBuilderInterface}
 * instance to generate a data grid of items.
 *
 * @package User Interface
 * @subpackage List Builder
 * @see ListBuilderScreenTrait
 */
interface ListBuilderScreenInterface extends AdminScreenInterface
{
    /**
     * Creates an instance of the list builder to use.
     * @return ListBuilderInterface
     */
    public function createListBuilder() : ListBuilderInterface;

    /**
     * Gets the ID of the list to be displayed, which can
     * be used to share its settings.
     *
     * @return string
     */
    public function getListID() : string;

    /**
     * Gets the fully configured ListBuilder instance.
     * @return ListBuilderInterface
     */
    public function getBuilder() : ListBuilderInterface;
}
