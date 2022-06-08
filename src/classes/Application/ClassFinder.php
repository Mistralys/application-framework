<?php
/**
 * @package Application
 * @subpackage Class loading
 * @see \Application\ClassFinder
 */

declare(strict_types=1);

namespace Application;

use Application\Exception\ClassFinderException;
use Application\Exception\ClassNotExistsException;
use Application\Exception\UnexpectedInstanceException;

/**
 * @package Application
 * @subpackage Class loading
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassFinder
{
     public const ERROR_CANNOT_RESOLVE_CLASS_NAME = 6428001;

    public static function resolveClass(string $legacyName) : ?string
    {
        // Handle cases where we have a mix of styles because of
        // get_class() used to build a class name.
        $legacyName = str_replace('\\', '_', $legacyName);

        if(class_exists($legacyName))
        {
            return $legacyName;
        }

        $nameNS = str_replace('_', '\\', $legacyName);

        if(class_exists($nameNS))
        {
            return $nameNS;
        }

        return null;
    }

    public static function requireResolvedClass(string $legacyName) : string
    {
        $class = self::resolveClass($legacyName);

        if($class !== null)
        {
            return $class;
        }

        throw new ClassFinderException(
            'Cannot resolve class name.',
            sprintf(
                'Legacy name: [%s] ',
                $legacyName
            ),
            self::ERROR_CANNOT_RESOLVE_CLASS_NAME
        );
    }

    public static function requireClassExists(string $className) : void
    {
        if(class_exists($className))
        {
            return;
        }

        throw new ClassNotExistsException($className);
    }

    /**
     * Requires the target class name to exist, and extend
     * or implement the specified class/interface. If it does
     * not, an exception is thrown.
     *
     * @param class-string $targetClass
     * @param class-string $extendsClass
     * @return void
     *
     * @throws UnexpectedInstanceException
     * @throws ClassNotExistsException
     */
    public static function requireClassExtends(string $targetClass, string $extendsClass) : void
    {
        self::requireClassExists($targetClass);

        if(is_a($targetClass, $extendsClass, true))
        {
            return;
        }

        throw new UnexpectedInstanceException($extendsClass, $targetClass);
    }

    /**
     * If the target object is not an instance of the target class
     * or interface, throws an exception.
     *
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @param object $object
     * @param int $errorCode
     * @return ClassInstanceType
     *
     * @throws ClassNotExistsException
     * @throws UnexpectedInstanceException
     */
    public static function requireInstanceOf(string $class, object $object, int $errorCode=0)
    {
        if(!class_exists($class) && !interface_exists($class) && !trait_exists($class))
        {
            throw new ClassNotExistsException($class, $errorCode);
        }

        if(is_a($object, $class, true))
        {
            return $object;
        }

        throw new UnexpectedInstanceException($class, $object, $errorCode);
    }
}
