<?php

declare(strict_types=1);

use Application\AppFactory;
use Application\Media\Collection\MediaRecord;
use Application\Media\DocumentTrait;
use Application\Media\MediaException;
use Application\Tags\Taggables\TagCollectionInterface;
use Application\Tags\Taggables\TaggableInterface;
use Application\Tags\Taggables\TaggableTrait;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime;
use AppUtils\Microtime_Exception;
use UI\AdminURLs\AdminURL;

abstract class Application_Media_Document
    implements
    Application_Media_DocumentInterface,
    TaggableInterface
{
    use Application_Traits_Loggable;
    use DocumentTrait;
    use TaggableTrait;

    public const ERROR_CONFIGURATION_TYPE_MISMATCH = 650001;
    public const ERROR_CANNOT_CHECK_PROCESSING_REQUIREMENTS = 650002;
    public const ERROR_NO_TRANSACTION_STARTED = 650003;
    public const ERROR_FILE_NOT_FOUND = 650004;
    public const ERROR_DATA_NOT_FOUND = 650005;

    protected array $data;
    protected Application_Media $media;
    protected int $id;

    /**
     * @param string|int $media_id
     * @throws DBHelper_Exception
     * @throws JsonException
     * @throws MediaException
     */
    protected function __construct($media_id)
    {
        $data = DBHelper::fetch(
            "SELECT
                *
            FROM
                `media`
            WHERE
                `media_id`=:media_id",
            array(
                ':media_id' => $media_id
            )
        );

        if($data === null) {
            throw new MediaException(
                'Cannot load media document data.',
                sprintf(
                    'No media document found in the database with ID [%s].',
                    $media_id
                ),
                self::ERROR_DATA_NOT_FOUND
            );
        }

        $this->id = (int)$media_id;
        $this->data = $data;
        $this->media = Application_Media::getInstance();
    }

    public function getID() : int
    {
        return $this->id;
    }

    /**
     * Creates a new media document. Note that this only handles creating
     * the record in the database, you have to make sure the target file
     * is created on disk as well.
     *
     * @param string $name
     * @param string $extension
     * @param Application_User|NULL $user
     * @param DateTime|NULL $date_added
     * @return Application_Media_Document
     */
    public static function createNew(string $name, string $extension, ?Application_User $user = null, ?DateTime $date_added = null) : Application_Media_Document
    {
        if (!$user instanceof Application_User) {
            $user = Application::getUser();
        }

        if (!$date_added instanceof DateTime) {
            $date_added = new DateTime();
        }

        $media = Application_Media::getInstance();
        $type = $media->getTypeByExtension($extension);

        $media_id = (int)DBHelper::insert(
            "INSERT INTO
                `media`
            SET
                `user_id`=:user_id,
                `media_date_added`=:media_date_added,
                `media_type`=:media_type,
                `media_name`=:media_name,
                `media_extension`=:media_extension",
            array(
                'user_id' => $user->getID(),
                'media_date_added' => $date_added->format('Y-m-d H:i:s'),
                'media_type' => $type,
                'media_name' => $name,
                'media_extension' => $extension
            )
        );

        return AppFactory::createMedia()->getByID($media_id);
    }

    protected ?string $path = null;

    /**
     * @return string
     * @throws Microtime_Exception
     */
    public function getPath() : string
    {
        if (!isset($this->path)) {
            $date = $this->getDateAdded();
            $this->path = sprintf(
                '%s/%s/%s/%s.%s',
                $this->media->getStorageFolder(),
                $date->format('Y'),
                $date->format('m'),
                $this->id,
                $this->getExtension()
            );
        }

        return $this->path;
    }

    /**
     * Runtime caching of the date object
     * @var DateTime|NULL
     */
    protected ?DateTime $dateAdded = null;

    /**
     * Retrieves the date that this document was added.
     * @return DateTime
     * @throws Microtime_Exception
     */
    public function getDateAdded() : DateTime
    {
        if (!isset($this->dateAdded)) {
            $this->dateAdded = Microtime::createFromString($this->data['media_date_added']);
        }

        return $this->dateAdded;
    }

    /**
     * Retrieves the document's extension. e.g. "jpg".
     * @return string
     */
    public function getExtension() : string
    {
        return (string)$this->data['media_extension'];
    }

    public function getName() : string
    {
        return (string)$this->data['media_name'];
    }

    public function getUserID() : int
    {
        return (int)$this->data['user_id'];
    }

    public static function createNewFromFile(string $name, FileInfo $file, ?Application_User $user=null, ?DateTime $dateAdded=null) : Application_Media_Document
    {
        $document = self::createNew(
            $name,
            $file->getExtension(),
            $user,
            $dateAdded
        );

        $document->setSourceFile($file);

        return $document;
    }

    /**
     * Creates a new media document from a previously uploaded file.
     *
     * @param Application_Uploads_Upload $upload
     * @return Application_Media_Document
     *
     * @throws Application_Exception
     * @throws DBHelper_Exception
     * @throws FileHelper_Exception
     */
    public static function createNewFromUpload(Application_Uploads_Upload $upload) : Application_Media_Document
    {
        // avoid doing this again if the document has already
        // been added, which can happen in some edge cases.
        if($upload->hasDocument()) {
            return $upload->getDocument();
        }
        
        $started = DBHelper::isTransactionStarted();
        $simulation = Application::isSimulation();
        
        if(!$started) {
            DBHelper::startTransaction();
        }
        
        // create the new document
        $document = self::createNew(
            $upload->getName(),
            $upload->getExtension(),
            $upload->getUser()
        );

        $upload->setDocument($document);
        
        $sourceFile = $upload->getPath();
        $targetFile = $document->getPath();

        if (!file_exists($sourceFile)) {
            throw new MediaException(
                'Uploaded file missing',
                sprintf(
                    'Tried finding the uploaded file [%1$s], but it seems to be missing.',
                    $sourceFile
                )
            );
        }

        $targetFolder = dirname($targetFile);
        FileHelper::createFolder($targetFolder);

        if (file_exists($targetFile) && !@unlink($targetFile)) {
            throw new MediaException(
                'Could not clean up existing files',
                sprintf(
                    'The target file [%1$s] already existed on disk, but could not be deleted to make room for the new file.',
                    $targetFile
                )
            );
        }

        FileHelper::copyFile($sourceFile, $targetFile);
        
        if(!$started) {
            if($simulation) {
                DBHelper::rollbackTransaction();
            } else {
                DBHelper::commitTransaction();
            }
        }
        
        return $document;
    }

    /**
     * Creates a media file by its ID. Uses a query to determine
     * the media type before instantiating the document.
     *
     * @param int $media_id
     * @return Application_Media_Document
     *
     * @throws MediaException
     * @throws BaseClassHelperException
     * @throws DBHelper_Exception
     */
    public static function create(int $media_id) : Application_Media_Document
    {
        $data = DBHelper::fetch(
            "SELECT
                `media_type`
            FROM
                `media`
            WHERE
                `media_id`=:media_id",
            array(
                ':media_id' => $media_id
            )
        );

        if (!is_array($data) || !isset($data['media_type'])) {
            throw new MediaException(
                'Unknown media file',
                sprintf(
                    'No record found in the database for the media file with ID [%1$s].',
                    $media_id
                ),
                self::ERROR_DATA_NOT_FOUND
            );
        }

        $class = ClassHelper::requireResolvedClass(Application_Media_Document::class.'_' . $data['media_type']);

        return ClassHelper::requireObjectInstanceOf(
            Application_Media_Document::class,
            new $class($media_id)
        );
    }

    /**
     * Human-readable label for this media type, e.g. "Image".
     * Must be implemented by the media type class.
     */
    abstract public static function getLabel() : string;

    abstract public static function getIcon() : UI_Icon;


    /**
     * List of supported file extensions for this media file type.
     * Must be implemented by the media type class.
     *
     * @return string[]
     */
    abstract public static function getExtensions() : array;

    abstract public function getMaxThumbnailSize() : int;

    /**
     * Retrieves an identification string for the media document that
     * is mainly used for logging purposes. Contains the document's ID
     * and filename.
     *
     * @return string
     */
    public function getIdentification() : string
    {
        return sprintf(
            'Media document [%1$s "%2$s"]',
            $this->id,
            $this->getFilename()
        );
    }

    protected ?string $cachedTypeID = null;
    
   /**
    * Retrieves the ID of the document type, e.g. <code>Image</code>.
    * @return string
    */
    public function getTypeID() : string
    {
        if(!isset($this->cachedTypeID)) {
            $this->cachedTypeID = ClassHelper::getClassTypeName($this);
        }
        
        return $this->cachedTypeID;
    }
    
    protected ?Application_Media_Configuration $config = null;

    /**
     * Sets the configuration for this media document,
     * when it is being pre-processed using the media processor.
     *
     * @param Application_Media_Configuration $config
     * @throws MediaException
     */
    public function setConfiguration(Application_Media_Configuration $config) : void
    {
        if($config->getTypeID() !== $this->getTypeID()) {
            throw new MediaException(
                'Media configuration error',
                sprintf(
                    'Cannot set a media configuration type [%s] for a media document of type [%s], the types have to match.',
                    $config->getTypeID(),
                    $this->getTypeID()    
                ),
                self::ERROR_CONFIGURATION_TYPE_MISMATCH    
            );
        }
        
        $this->config = $config;
    }
    
   /**
    * Checks whether a media configuration has been set for the document.
    * @return boolean
    */
    public function hasConfiguration() : bool
    {
        return isset($this->config);
    }
    
   /**
    * Retrieves the document's media configuration.
    *
    * NOTE: this is not always available.
    * 
    * @return Application_Media_Configuration|NULL
    */
    public function getConfiguration() : ?Application_Media_Configuration
    {
        return $this->config;
    }
    
   /**
    * Checks whether this media document has to be pre-processed.
    * 
    * @throws MediaException
    * @return boolean
    */
    public function isProcessRequired() : bool
    {
        if(isset($this->config)) {
            return $this->config->isProcessRequired($this);
        }

        throw new MediaException(
            'Media configuration missing',
            sprintf(
                'Cannot check if the media document [%s] of type [%s] requires processing, no media configuration has been set.',
                $this->getID(),
                $this->getTypeID()
            ),
            self::ERROR_CANNOT_CHECK_PROCESSING_REQUIREMENTS
        );
    }
    
    public function isUpload() : bool
    {
        return false;
    }
    
    public function upgrade() : Application_Media_Document
    {
        return $this;
    }
    
   /**
    * Retrieves the form value to use for the document.
    * @return array{name:string,state:string,id:int}
    */
    public function toFormValue() : array
    {
        return array(
            'name' => $this->getName(),
            'state' => $this->getMediaSourceID(),
            'id' => $this->getID()
        );
    }

    /**
     * Encodes the image's binary data to base64.
     * @return string|NULL
     *
     * @throws MediaException {@see self::ERROR_FILE_NOT_FOUND}
     */
    public function toBase64() : ?string
    {
        $path = $this->getPath();

        if(file_exists($path)) {
            return base64_encode(file_get_contents($path));
        }
        
        throw new MediaException(
            'Media file not found',
            sprintf(
                'The media file [#%s] could not be found in target path [%s].',
                $this->getID(),
                $path
            ),
            self::ERROR_FILE_NOT_FOUND
        );
    }

    /**
     * @return void
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     * @throws JsonException
     * @throws MediaException
     */
    public function delete() : void
    {
        $this->log('Requested to delete. Simulation: '.ConvertHelper::bool2string(Application::isSimulation(), true));

        if(!DBHelper::isTransactionStarted()) {
            throw new MediaException(
                'No transaction started',
                'A database transaction needs to be present to delete a media document.',
                self::ERROR_NO_TRANSACTION_STARTED    
            );
        }
        
        $path = $this->getPath();
        if(file_exists($path) && !Application::isSimulation() && !unlink($path)) {
            $this->log('Could not delete the file on disk.');
        }
        
        DBHelper::delete(
            "DELETE FROM
                `media`
            WHERE
                `media_id`=:primary",
            array(
                'primary' => $this->id
            )    
        );
        
        $this->log('Deletion complete.');
    }
    
    public function getLogIdentifier() : string
    {
        return sprintf(
            'Media document [#%s] | Name [%s]',
            $this->getID(),
            $this->getFilename()
        );
    }

    /**
     * Sets/overwrites the document from the specified file.
     *
     * @param FileInfo $file
     * @return $this
     * @throws FileHelper_Exception
     */
    public function setSourceFile(FileInfo $file) : self
    {
        $file->copyTo($this->getPath());
        return $this;
    }

    public function getFileInfo() : FileInfo
    {
        return FileInfo::factory($this->getPath());
    }

    public function getTypeIcon() : UI_Icon
    {
        return static::getIcon()->setTooltip($this->getTypeLabel());
    }

    public function getTypeLabel() : string
    {
        return static::getLabel();
    }

    abstract public function injectMetadata(UI_PropertiesGrid $grid) : void;

    // region: Tagging

    public function getTaggableLabel(): string
    {
        return $this->getName();
    }

    public function getTagCollection(): TagCollectionInterface
    {
        return $this->media;
    }

    public function getTagRecordPrimaryValue(): int
    {
        return $this->getID();
    }

    public function adminURLTagging() : AdminURL
    {
        return $this->getRecord()->adminURL()->tagging();
    }

    public function isTaggingEnabled(): bool
    {
        return $this->getTagCollection()->isTaggingEnabled();
    }

    // endregion

    public function getRecord() : MediaRecord
    {
        return AppFactory::createMediaCollection()->getByID($this->getID());
    }
}
