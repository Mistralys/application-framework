<?php
/**
 * File containing the {@see Application_Exception_UnexpectedInstanceType} class.
 *
 * @package Application
 * @subpackage Core
 * @see Application_Exception_UnexpectedInstanceType
 */

declare(strict_types=1);

use Application\Exception\UnexpectedInstanceException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use function AppUtils\parseVariable;

/**
 * Exception to use when an instanceof call fails, to signify 
 * that the expected class instance was not specified.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @deprecated Use the {@see \AppUtils\ClassHelper} methods instead.
 */
class Application_Exception_UnexpectedInstanceType extends ClassNotImplementsException
{
    /**
    * @param string $expectedClass
    * @param mixed $given
    * @param int $code
    * @param Throwable|null $previous
    */
    public function __construct(string $expectedClass, $given, int $code=0, ?Throwable $previous=null)
    {
        if($code === 0)
        {
            $code = UnexpectedInstanceException::ERROR_UNEXPECTED_INSTANCE_TYPE;
        }
        
        parent::__construct(
            'Unexpected class instance', 
            sprintf(
                'Expected an instance of [%s], given: [%s].',
                $expectedClass,
                parseVariable($given)->enableType()->toString()
            ), 
            $code,
            $previous
        );
    }
}
