<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\AppFactory;
use Application\Tags\TagCollectionRegistry;
use Application\Tags\TaggingException;
use AppUtils\ConvertHelper;
use AppUtils\OperationResult;

/**
 * Unique taggable ID parser for taggable records.
 * Can fetch the according records.
 *
 * ## Usage
 *
 * 1. Parse an ID using {@see self::parse()}.
 * 2. Check if the format is valid using {@see self::isValid()}.
 * 3. Get the taggable record using {@see self::getTaggable()}.
 *
 * > NOTE: Whether the taggable exists is not checked in
 * > the validation, only the format. This avoids calling
 * > the database when the ID is not used.
 *
 * @package Application
 * @subpackage Tags
 */
class TaggableUniqueID extends OperationResult
{
    public const ID_SEPARATOR_CHAR = '.';

    public const VALIDATION_ERROR_UNKNOWN_COLLECTION = 167701;
    public const VALIDATION_ERROR_INVALID_FORMAT = 167702;

    /**
     * @var array<string, TaggableUniqueID>
     */
    private static array $parsed = array();
    private string $uniqueID;
    private string $collectionID = '';
    private int $primaryKey = 0;

    public function __construct(string $uniqueID)
    {
        $this->uniqueID = $uniqueID;

        parent::__construct($this);

        $this->_parse();
    }

    public static function parse(string $uniqueID) : self
    {
        if(!isset(self::$parsed[$uniqueID])) {
            self::$parsed[$uniqueID] = new self($uniqueID);
        }

        return self::$parsed[$uniqueID];
    }

    private function _parse() : void
    {
        $parts = ConvertHelper::explodeTrim(self::ID_SEPARATOR_CHAR, $this->uniqueID);

        if(count($parts) !== 2) {
            $this->makeError(
                t('Invalid ID format.'),
                self::VALIDATION_ERROR_INVALID_FORMAT
            );
        }

        $this->collectionID = $parts[0];
        $this->primaryKey = (int)$parts[1];

        if(!$this->getRegistry()->idExists($this->collectionID)) {
            $this->makeError(
                t('Unknown tag collection ID.'),
                self::VALIDATION_ERROR_UNKNOWN_COLLECTION
            );
        }
    }

    /**
     * Throws a taggable exception if the unique ID's format is invalid
     * or the taggable record does not exist.
     *
     * @return $this
     * @throws TaggingException
     */
    public function requireExists() : self
    {
        $this->requireValid();

        if($this->taggableExists()) {
            return $this;
        }

        throw new TaggingException(
            'Taggable record not found.',
            sprintf(
                'The taggable record with the unique ID [%s] was not found.',
                $this->uniqueID
            ),
            TaggingException::ERROR_TAGGABLE_NOT_FOUND
        );
    }

    /**
     * Throws a taggable exception if the unique ID's format is invalid.
     * @return $this
     * @throws TaggingException
     */
    public function requireValid() : self
    {
        if($this->isValid()) {
            return $this;
        }

        throw new TaggingException(
            'Invalid taggable record unique ID format.',
            sprintf(
                'The unique ID must be in the format [{collectionID}%s{primaryKey}], but [%s] was given.',
                self::ID_SEPARATOR_CHAR,
                $this->uniqueID
            ),
            TaggingException::ERROR_INVALID_UNIQUE_ID
        );
    }

    public function taggableExists() : bool
    {
        return $this->isValid() && $this->getCollection()->idExists($this->primaryKey);
    }

    public function getTaggable() : TaggableInterface
    {
        return $this->getCollection()->getTaggableByID($this->primaryKey);
    }

    public function getCollection() : TagCollectionInterface
    {
        return $this->getRegistry()->getByID($this->collectionID);
    }

    public function getCollectionID() : string
    {
        return $this->collectionID;
    }

    public function getPrimaryKey() : int
    {
        return $this->primaryKey;
    }

    public function getRegistry() : TagCollectionRegistry
    {
        return AppFactory::createTags()->createCollectionRegistry();
    }

    /**
     * @param TaggableInterface|Taggable $taggable
     * @return string
     */
    public static function compileUniqueID($taggable) : string
    {
        if($taggable instanceof TaggableInterface) {
            $collectionID = $taggable->getTagCollection()->getCollectionID();
            $primaryKey = $taggable->getTagManager()->getPrimaryKey();
        } else {
            $collectionID = $taggable->getCollection()->getCollectionID();
            $primaryKey = $taggable->getPrimaryKey();
        }

        return sprintf(
            '%s%s%s',
            $collectionID,
            self::ID_SEPARATOR_CHAR,
            $primaryKey
        );
    }

    public function __toString() : string
    {
        return $this->uniqueID;
    }
}
