<?php
/**
 * @package Disposables
 * @subpackage Attributes
 */

declare(strict_types=1);

namespace Application\Disposables\Attributes;

use Application\Disposables\DisposableInterface;
use Attribute;

/**
 * Attribute to mark a class or method as being
 * aware of disposables, meaning it properly
 * handles disposable objects and their lifecycle.
 *
 * @package Disposables
 * @subpackage Attributes
 *
 * @see DisposableInterface
 */
#[Attribute]
class DisposedAware
{

}
