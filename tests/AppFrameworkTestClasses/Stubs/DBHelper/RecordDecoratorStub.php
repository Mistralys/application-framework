<?php
/**
 * @package DBHelper
 * @subpackage Decorators
 */

declare(strict_types=1);

namespace AppFrameworkTestClasses\Stubs\DBHelper;

use DBHelper\BaseRecord\BaseRecordDecorator;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Stub class used to test record decorators.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
class RecordDecoratorStub extends BaseRecordDecorator
{
    private DBHelperRecordInterface $record;

    public function __construct(DBHelperRecordInterface $record)
    {
        $this->record = $record;
    }

    public function getDecoratedRecord(): DBHelperRecordInterface
    {
        return $this->record;
    }
}
