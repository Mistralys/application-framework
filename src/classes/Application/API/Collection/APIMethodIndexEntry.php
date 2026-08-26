<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\API\Collection;

use Application\API\APIMethodInterface;

/**
 * Typed entry for the API method index, carrying the method's
 * name, class, declared right and group ID.
 *
 * Used by {@see APIMethodIndex} for JSON round-tripping via
 * {@see toArray()} and {@see fromArray()}.
 *
 * @package API
 * @subpackage Method Collection
 */
final class APIMethodIndexEntry
{
    public const string KEY_METHOD_NAME = 'methodName';
    public const string KEY_CLASS_NAME = 'className';
    public const string KEY_REQUIRED_RIGHT = 'requiredRight';
    public const string KEY_GROUP_ID = 'groupID';

    /**
     * @param string $methodName
     * @param class-string<APIMethodInterface> $className
     * @param string|null $requiredRight
     * @param string $groupID
     */
    public function __construct(
        private string $methodName,
        private string $className,
        private ?string $requiredRight,
        private string $groupID
    )
    {
    }

    public function getMethodName() : string
    {
        return $this->methodName;
    }

    /**
     * @return class-string<APIMethodInterface>
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    public function getRequiredRight() : ?string
    {
        return $this->requiredRight;
    }

    public function getGroupID() : string
    {
        return $this->groupID;
    }

    /**
     * @return array<string,string|null>
     */
    public function toArray() : array
    {
        return array(
            self::KEY_METHOD_NAME => $this->methodName,
            self::KEY_CLASS_NAME => $this->className,
            self::KEY_REQUIRED_RIGHT => $this->requiredRight,
            self::KEY_GROUP_ID => $this->groupID,
        );
    }

    /**
     * @param array<string,string|null> $data
     * @return self
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            (string)($data[self::KEY_METHOD_NAME] ?? ''),
            (string)($data[self::KEY_CLASS_NAME] ?? ''),
            $data[self::KEY_REQUIRED_RIGHT] ?? null,
            (string)($data[self::KEY_GROUP_ID] ?? ''),
        );
    }
}
