<?php

abstract class Application_Media_Document implements Application_Media_DocumentInterface
{
    use Application_Traits_Loggable;

    const ERROR_CONFIGURATION_TYPE_MISMATCH = 650001;
    const ERROR_CANNOT_CHECK_PROCESSING_REQUIREMENTS = 650002;
    const ERROR_NO_TRANSACTION_STARTED = 650003;
    
    protected $data;

    /**
     * @var Application_Media
     */
    protected $media;

    protected $id;

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

        $this->id = $media_id;
        $this->data = $data;
        $this->media = Application_Media::getInstance();
    }

    public function getID()
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
     * @param Application_User $user
     * @param DateTime $date_added
     * @return Application_Media_Document
     */
    public static function createNew($name, $extension, $user = null, $date_added = null)
    {
        if (!$user instanceof Application_User) {
            $user = Application::getUser();
        }

        if (!$date_added instanceof DateTime) {
            $date_added = new DateTime();
        }

        $media = Application_Media::getInstance();
        $type = $media->getTypeByExtension($extension);

        $media_id = intval(DBHelper::insert(
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
        ));

        return self::create($media_id);
    }

    protected $path;

    public function getPath()
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
     * @var DateTime
     */
    protected $dateAdded;

    /**
     * Retrieves the date that this document was added.
     * @return DateTime
     */
    public function getDateAdded()
    {
        if (!isset($this->dateAdded)) {
            $this->dateAdded = new DateTime($this->data['media_date_added']);
        }

        return $this->dateAdded;
    }

    /**
     * Retrieves the document's extension. e.g. "jpg".
     * @return string
     */
    public function getExtension()
    {
        return $this->data['media_extension'];
    }

    /**
     * Retrieves the size of the media file on disk, in bytes.
     * @return number
     */
    public function getFilesize()
    {
        $size = filesize($this->getPath());
        if($size !== false) {
            return $size;
        }

        return 0;
    }

    /**
     * Retrieves the size of the media file in a human redable format,
     * e.g. 15 Kb.
     *
     * @return string
     */
    public function getFilesizeReadable()
    {
        return AppUtils\ConvertHelper::bytes2readable($this->getFilesize());
    }

    public function getName()
    {
        return $this->data['media_name'];
    }

    /**
     * Retrieves the media document's file name, e.g.:
     *
     * media_name.jpg
     *
     * Note that this is NOT the filename on disk, but the
     * filename as defined by the user that should be used
     * when the media file is downloaded or used in the exports.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->getName() . '.' . $this->getExtension();
    }

    /**
     * Retrieves the user object of the user that created this
     * media document.
     *
     * @return Application_User
     */
    public function getUser()
    {
        return Application_User::createByID($this->data['user_id']);
    }

    /**
     * Creates a new media document from a previously uploaded file.
     *
     * @param Application_Uploads_Upload $upload
     * @return Application_Media_Document
     */
    public static function createNewFromUpload(Application_Uploads_Upload $upload)
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
            throw new Application_Exception(
                'Uploaded file missing',
                sprintf(
                    'Tried finding the uploaded file [%1$s], but it seems to be missing.',
                    $sourceFile
                )
            );
        }

        $targetFolder = dirname($targetFile);
        if (!file_exists($targetFolder) && !@mkdir($targetFolder, 0777, true)) {
            throw new Application_Exception(
                'Failed creating media folder',
                sprintf(
                    'Tried finding and creating the folder [%1$s] to copy a media file to, but creating it failed.',
                    $targetFolder
                )
            );
        }

        if (file_exists($targetFile) && !@unlink($targetFile)) {
            throw new Application_Exception(
                'Could not clean up existing files',
                sprintf(
                    'The target file [%1$s] already existed on disk, but could not be deleted to make room for the new file.',
                    $targetFile
                )
            );
        }

        // copy the existing file on disk to the new location
        if (!@copy($sourceFile, $targetFile)) {
            throw new Application_Exception(
                'Failed copying media file',
                sprintf(
                    'Tried creating a new media document from an existing upload, but copying the uploaded file [%1$s] to its destination [%2$s] failed.',
                    $sourceFile,
                    $targetFile
                )
            );
        }
        
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
     * Creates a media file by its ID.
     *
     * @param int $media_id
     * @throws Application_Exception
     * @return Application_Media_Document
     */
    public static function create($media_id)
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
            throw new Application_Exception(
                'Unknown media file',
                sprintf(
                    'No record found in the database for the media file with ID [%1$s].',
                    $media_id
                )
            );
        }

        $class = 'Application_Media_Document_' . $data['media_type'];
        Application::requireClass($class);

        return new $class($media_id);
    }

    /**
     * Human readable label for this media type, e.g. "Image".
     * Must be implemented by the media type class.
     *
     * @throws Application_Exception
     */
    public static function getLabel()
    {
        throw new Application_Exception(
            'Has to be implemented in child class'
        );
    }

    /**
     * List of supported file extensions for this media file type.
     * Must be implemented by the media type class.
     *
     * @throws Application_Exception
     */
    public static function getExtensions()
    {
        throw new Application_Exception(
            'Has to be implemented in child class'
        );
    }

    /**
     * Retrieves the full URL to the media script to display a thumbnail
     * of the media file. The width and height parameters can be set as
     * needed to resample the thumbnail to the target size.
     *
     * @param string $width
     * @param string $height
     * @return string
     */
    public function getThumbnailURL($width = null, $height = null)
    {
        return APP_URL . '/media.php?' . http_build_query(array(
            'source' => 'media',
            'media_id' => $this->id,
            'width' => $width,
            'height' => $height
        ));
    }

    /**
     * Checks whether the cached thumbnail file for the specified
     * size exists on disk.
     *
     * @param string $width
     * @param string $height
     * @return boolean
     */
    abstract public function thumbnailExists($width = null, $height = null);

    /**
     * Retrieves an identification string for the media document that
     * is mainly used for logging purposes. Contains the document's ID
     * and file name.
     *
     * @return string
     */
    public function getIdentification()
    {
        return sprintf(
            'Media document [%1$s "%2$s"]',
            $this->id,
            $this->getFilename()
        );
    }

    /**
     * Creates a thumbnail of the image for the specified dimensions.
     * Width and height can be omitted as needed to constrain resampling
     * to one or none of the sides.
     *
     * Returns the path to the thumbnail file when successful.
     *
     * @return string
     * @throws Application_Exception
     */
    abstract public function createThumbnail($width = null, $height = null);

    protected $cachedTypeID;
    
   /**
    * Retrieves the ID of the document type, e.g. <code>Image</code>.
    * @return string
    */
    public function getTypeID()
    {
        if(!isset($this->cachedTypeID)) {
            $this->cachedTypeID = str_replace('Application_Media_Document_', '', get_class($this));
        }
        
        return $this->cachedTypeID;
    }
    
   /**
    * @var Application_Media_Configuration
    */
    protected $config;
    
   /**
    * Sets the configuration to use for this media document,
    * when it is being pre-processed using the media processor.
    * 
    * @param Application_Media_Configuration $config
    */
    public function setConfiguration(Application_Media_Configuration $config)
    {
        if($config->getTypeID() != $this->getTypeID()) {
            throw new Application_Exception(
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
    public function hasConfiguration()
    {
        return isset($this->config);
    }
    
   /**
    * Retrieves the document's media configuration.
    * NOTE: this is not always available, so you should check
    * if it is using the {@link hasConfiguration} method.
    * 
    * @return Application_Media_Configuration
    */
    public function getConfiguration()
    {
        return $this->config;
    }
    
   /**
    * Checks whether this media document has to be pre-processed.
    * 
    * @throws Application_Exception
    * @return boolean
    */
    public function isProcessRequired()
    {
        if(!$this->hasConfiguration()) {
            throw new Application_Exception(
                'Media configuration missing',
                sprintf(
                    'Cannot check if the media document [%s] of type [%s] requires processing, no media configuration has been set.',
                    $this->getID(),
                    $this->getTypeID()    
                ),
                self::ERROR_CANNOT_CHECK_PROCESSING_REQUIREMENTS
            );
        }
        
        return $this->config->isProcessRequired($this);
    }
    
    public function isUpload()
    {
        return false;
    }
    
    public function upgrade()
    {
        return $this;
    }
    
   /**
    * Retrieves the form value to use for the document with the
    * @return array
    */
    public function toFormValue()
    {
        return array(
            'name' => $this->getName(),
            'state' => 'media',
            'id' => $this->getID()
        );
    }
    
   /**
    * Encodes the image's binary data to base64.
    * @return string|NULL
    */
    public function toBase64()
    {
        $path = $this->getPath();
        if(file_exists($path)) {
            return base64_encode(file_get_contents($path));
        }
        
        return null;
    }
    
    public function delete()
    {
        $this->log('Requested to delete. Simulation: '.AppUtils\ConvertHelper::bool2string(Application::isSimulation(), true));

        if(!DBHelper::isTransactionStarted()) {
            throw new Application_Exception(
                'No transaction started',
                'A database transaction needs to be present to delete a media document.',
                self::ERROR_NO_TRANSACTION_STARTED    
            );
        }
        
        $path = $this->getPath();
        if(file_exists($path) && !Application::isSimulation()) {
            if(!unlink($path)) {
                $this->log('Could not delete the file on disk.');
            }
        }
        
        DBHelper::delete(
            "DELETE FROM
                `media`
            WHERE
                `media_id`=:media_id",
            array(
                'media_id' => $this->id
            )    
        );
        
        $this->log('Deletion complete.');
    }
    
   /**
    * Logs messages for the document.
    * @param string $message
    */
    protected function log($message)
    {
        Application::log('Media document ['.$this->getID().'] | '.$message);
    }
    
    public function isTypeSVG()
    {
        return strtolower($this->getExtension()) == 'svg';
    }
    
    public function isVector()
    {
        return $this->isTypeSVG();
    }

    public function getLogIdentifier() : string
    {
        return sprintf(
            'Media document [#%s] | Name [%s] | Size [%s]',
            $this->getID(),
            $this->getFilename(),
            $this->getFilesizeReadable()
        );
    }
}