<?php
/**
 * @package Application
 * @subpackage Class loading
 * @see \Application\Exception\ClassNotExistsException
 */

declare(strict_types=1);

namespace Application\Exception;

use Application;
use Throwable;

/**
 * @package Application
 * @subpackage Class loading
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassNotExistsException extends ClassFinderException
{
    public function __construct(string $className, int $errorCode=0, ?Throwable $previous=null)
    {
        if($errorCode === 0)
        {
            $errorCode = Application::ERROR_CLASS_NOT_FOUND;
        }

        parent::__construct(
            'Class does not exist',
            sprintf(
                'The class [%s] cannot be auto-loaded.',
                $className
            ),
            $errorCode,
            $previous
        );
    }
}
