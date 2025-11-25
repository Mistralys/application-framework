<?php
/**
 * @package DBHelper
 * @subpackage Decorators
 */

declare(strict_types=1);

namespace DBHelper\Traits;

use DBHelper\BaseRecord\BaseRecordDecorator;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Interface for a class that acts as a decorator for a {@see DBHelperRecordInterface}.
 *
 * ## Usage via trait
 *
 * 1. Implement this interface in your decorator class.
 * 2. Use the trait {@see RecordDecoratorTrait} to automatically forward method calls to the decorated record.
 * 3. Use additional traits as illustrated by {@see BaseRecordDecorator}.
 * 4. Implement the remaining methods.
 *
 * ## Usage via base class
 *
 * 1. Extend the class {@see BaseRecordDecorator}.
 * 2. Implement the remaining interface methods.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
interface RecordDecoratorInterface extends DBHelperRecordInterface
{
    public function getDecoratedRecord() : DBHelperRecordInterface;
}
