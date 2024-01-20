<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use DBHelper;

/**
 * Helper class that is used to register root tags with
 * a unique identifier, so their ID can be different
 * across different installations.
 *
 * Example: The root tag used for the media management.
 * It is created dynamically when needed, which means that
 * its ID can be different on each installation. Using
 * the registry, it can be identified reliably.
 *
 * @package Application
 * @subpackage Tags
 */
class TagRegistry
{
    public const COL_KEY = 'registry_key';
    public const COL_TAG_ID = TagCollection::PRIMARY_NAME;

    public const ERROR_KEY_ALREADY_REGISTERED = 149001;
    public const ERROR_KEY_NOT_REGISTERED = 149002;

    public static function isKeyRegistered(string $key) : bool
    {
        return self::getTagIDByKey($key) !== null;
    }

    public static function getTagIDByKey(string $key) : ?int
    {
        $tagID = DBHelper::createFetchKey(
            self::COL_TAG_ID,
            TagCollection::TABLE_REGISTRY
        )
            ->whereValue(self::COL_KEY, $key)
            ->fetchInt();

        if($tagID > 0) {
            return $tagID;
        }

        return null;
    }

    public static function getTagByKey(string $key) : TagRecord
    {
        $id = self::getTagIDByKey($key);

        if($id !== null) {
            return AppFactory::createTags()->getByID($id);
        }

        throw new TaggingException(
            'Tag registry key not found.',
            sprintf(
                'Tag registry key [%s] not found.',
                $key
            ),
            self::ERROR_KEY_NOT_REGISTERED
        );
    }

    public static function registerKey(string $key, string $tagLabel) : TagRecord
    {
        if(self::isKeyRegistered($key))
        {
            throw new TaggingException(
                'A tag registry key has already been registered.',
                sprintf(
                    'Key [%s] is already registered.',
                    $key
                ),
                self::ERROR_KEY_ALREADY_REGISTERED
            );
        }

        $tag = AppFactory::createTags()->createNewTag($tagLabel);

        DBHelper::insertDynamic(
            TagCollection::TABLE_REGISTRY,
            array(
                TagCollection::PRIMARY_NAME => $tag->getID(),
                self::COL_KEY => $key,
            )
        );

        return $tag;
    }
}
