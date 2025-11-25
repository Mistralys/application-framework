<?php
/**
 * @package DBHelper
 * @subpackage Decorators
 */

declare(strict_types=1);

namespace DBHelper\BaseRecord;

use Application\Disposables\DisposableTrait;
use Application_Traits_Eventable;
use Application_Traits_Loggable;
use DBHelper\Traits\RecordDecoratorInterface;
use DBHelper\Traits\RecordDecoratorTrait;

/**
 * Abstract base class that can be used to implement a record decorator.
 * It uses the {@see RecordDecoratorTrait} to forward method calls to the
 * decorated record.
 *
 * Alternatively, use the {@see RecordDecoratorTrait} directly in your own
 * class along with the other traits used here.
 *
 * @package DBHelper
 * @subpackage Decorators
 */
abstract class BaseRecordDecorator implements RecordDecoratorInterface
{
    use RecordDecoratorTrait;
    use Application_Traits_Loggable;
    use DisposableTrait;
    use Application_Traits_Eventable;
}
