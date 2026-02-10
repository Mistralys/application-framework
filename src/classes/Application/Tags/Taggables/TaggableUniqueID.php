<?php
/**
 * @package Tagging
 * @subpackage Taggables
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
 * @package Tagging
 * @subpackage Taggables
 */
class TaggableUniqueID extends OperationResult
{
    public const string ID_SEPARATOR_CHAR = '.';

    public const int VALIDATION_UNKNOWN_COLLECTION = 167701;
    public const int VALIDATION_INCORRECT_AMOUNT_OF_TOKENS = 167702;
    public const int VALIDATION_MISSING_SEPARATOR = 167703;
    public const int VALIDATION_NON_NUMERIC_RECORD_ID = 167704;
    public const int VALIDATION_ZERO_OR_NEGATIVE_RECORD_ID = 167705;

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
        if(strpos($this->uniqueID, self::ID_SEPARATOR_CHAR) === false) {
            $this->makeError(
                t('Invalid ID format, missing separator character.'),
                self::VALIDATION_MISSING_SEPARATOR
            );
            return;
        }

        $tokens = ConvertHelper::explodeTrim(self::ID_SEPARATOR_CHAR, $this->uniqueID);

        if(count($tokens) !== 2) {
            $this->makeError(
                t('Invalid ID format, unexpected amount of tokens.'),
                self::VALIDATION_INCORRECT_AMOUNT_OF_TOKENS
            );
            return;
        }

        if(!is_numeric($tokens[1])) {
            $this->makeError(
                t('Invalid taggable record ID, must be numeric.'),
                self::VALIDATION_NON_NUMERIC_RECORD_ID
            );
            return;
        }

        $this->collectionID = $tokens[0];
        $this->primaryKey = (int)$tokens[1];

        if($this->primaryKey < 1) {
            $this->makeError(
                t('Invalid taggable record ID, must be greater than zero.'),
                self::VALIDATION_ZERO_OR_NEGATIVE_RECORD_ID
            );
        }

        if(!$this->getRegistry()->idExists($this->collectionID)) {
            $this->makeError(
                t('Unknown tag collection ID.'),
                self::VALIDATION_UNKNOWN_COLLECTION
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

    public function getUniqueID() : string
    {
        return $this->uniqueID;
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
