<?php

use Application\Media\MediaException;
use Application\Uploads\LocalFileUpload;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper\FileInfo;

class Application_Media
{
    public const ERROR_UNKNOWN_MEDIA_CONFIGURATION = 680001;
    public const ERROR_NOT_AN_IMAGE_MEDIA_FILE = 680002;

    protected static ?Application_Media $instance = null;
    protected string $storageFolder;
    protected Application_Driver $driver;

    /**
     * Retrieves the global instance of the media manager. Creates
     * the instance as needed.
     *
     * @return Application_Media
     */
    public static function getInstance() : Application_Media
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_Media();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->storageFolder = Application::getStorageSubfolderPath('media');
        $this->driver = Application_Driver::getInstance();
    }

    /**
     * Retrieves the full path to the folder where media files are stored.
     * @return string
     */
    public function getStorageFolder() : string
    {
        return $this->storageFolder;
    }

    public function createFromFile(string $name, FileInfo $file, ?Application_User $user=null, ?DateTime $dateAdded=null) : Application_Media_Document
    {
        return Application_Media_Document::createNewFromFile(
            $name,
            $file,
            $user,
            $dateAdded
        );
    }

    /**
     * Creates an image media document from a local file path.
     *
     * @param string $name
     * @param FileInfo $file
     * @param Application_User|null $user
     * @param DateTime|null $dateAdded
     * @return Application_Media_Document_Image
     * @throws MediaException
     */
    public function createImageFromFile(string $name, FileInfo $file, ?Application_User $user=null, ?DateTime $dateAdded=null) : Application_Media_Document_Image
    {
        try
        {
            return ClassHelper::requireObjectInstanceOf(
                Application_Media_Document_Image::class,
                $this->createFromFile($name, $file, $user, $dateAdded)
            );
        }
        catch (BaseClassHelperException $e)
        {
            throw new MediaException(
                'Created media document is not an image.',
                sprintf(
                    'Source file: [%s].',
                    $file->getPath()
                ),
                self::ERROR_NOT_AN_IMAGE_MEDIA_FILE,
                $e
            );
        }
    }

    /**
     * Creates a new media document from a previously uploaded file.
     * Returns the new media document. Note that this does not delete
     * the upload: that has to be done manually as needed.
     *
     * @param Application_Uploads_Upload $upload
     * @return Application_Media_Document
     * @throws Application_Exception
     */
    public function createFromUpload(Application_Uploads_Upload $upload) : Application_Media_Document
    {
        return Application_Media_Document::createNewFromUpload($upload);
    }

    /**
     * Retrieves a media document by its ID.
     * Throws an exception if it does not exist in the database.
     *
     * @param int $media_id
     * @return Application_Media_Document
     * @throws Application_Exception
     */
    public function getByID($media_id)
    {
        return Application_Media_Document::create($media_id);
    }
    
   /**
    * Attempts to retrieve a media document from a form value.
    * @param array $value
    * @return Application_Media_Document|NULL
    */
    public function getByFormValue($value)
    {
        if($this->isMediaFormValue($value)) {
            return $this->getByID($value['id']); 
        }
        
        return null;
    }
    
    public function isMediaFormValue($value)
    {
        if(!is_array($value)) {
            return false;
        }
        
        if(!isset($value['state'])) {
            return false;
        }
        
        if(!in_array($value['state'], array('media', 'upload'))) {
            return false;
        }
        
        return true;
    }

    /**
     * Retrieves the media type ID for the specified extension,
     * or NULL if no types exist to handle that extension.
     *
     * @param string $extension
     * @return NULL|string
     */
    public function getTypeByExtension($extension)
    {
        $this->loadTypes();
        if (isset($this->extensions[$extension])) {
            return $this->extensions[$extension];
        }

        return null;
    }

    /**
     * @var array<string,string>|NULL
     */
    protected ?array $extensions = null;

    /**
     * @var array<string,array{label:string,extensions:array<int,string>}>|NULL
     */
    protected ?array $types = null;

    protected function loadTypes() : void
    {
        if (isset($this->extensions))
        {
            return;
        }

        $this->extensions = array();
        $this->types = array();

        $folder = $this->driver->getApplication()->getClassesFolder() . '/Application/Media/Document';
        $d = new DirectoryIterator($folder);

        foreach ($d as $item)
        {
            if (!$item->isFile())
            {
                continue;
            }

            $info = pathinfo($item->getFilename());
            if (!isset($info['extension']) || strtolower($info['extension']) !== 'php') {
                continue;
            }

            $id = ConvertHelper::filenameRemoveExtension($info['basename']);
            $class = ClassHelper::requireResolvedClass(Application_Media_Document::class.'_' . $id);

            $extensions = call_user_func(array($class, 'getExtensions'));
            foreach ($extensions as $extension) {
                $this->extensions[$extension] = $id;
            }

            $this->types[$id] = array(
                'label' => (string)call_user_func(array($class, 'getLabel')),
                'extensions' => $extensions
            );
        }
    }

    /**
     * Creates a media configuration instance. These are document
     * type specific, and are used to store configurations for
     * media pre-processing using the media processor class. For
     * example, they are used to store the size presets to resize
     * images.
     *
     * @param string $type The configuration type, e.g. "Image". Case sensitive.
     * @return Application_Media_Configuration
     *
     * @throws ClassHelper\ClassNotExistsException
     * @throws ClassHelper\ClassNotImplementsException
     * @throws Throwable
     */
    public function createConfiguration(string $type) : Application_Media_Configuration
    {
        $class = ClassHelper::requireResolvedClass(Application_Media_Configuration::class.'_'.$type);

        return ClassHelper::requireObjectInstanceOf(
            Application_Media_Configuration::class,
            new $class()
        );
    }
    
   /**
    * @param integer $media_id
    * @return boolean
    */
    public function idExists($media_id) 
    {
        return DBHelper::keyExists('media', array('media_id' => $media_id));
    }
    
    public function configurationIDExists($config_id)
    {
        return DBHelper::keyExists('media_configurations', array('config_id' => $config_id));
    }
    
    public function getConfigurationByID($config_id)
    {
        $data = DBHelper::fetch(
            "SELECT
                `type_id`
            FROM
                `media_configurations`
            WHERE
                `config_id`=:config_id",
            array(
                'config_id' => $config_id
            )    
        );
        
        if(!is_array($data) || !isset($data['type_id'])) {
            throw new Application_Exception(
                'Unknown media configuration',
                sprintf(
                    'Could not retrieve the media configuration [%s] from the database.',
                    $config_id    
                ),
                self::ERROR_UNKNOWN_MEDIA_CONFIGURATION
            );
        }
        
        $config = $this->createConfiguration($data['type_id']);
        $config->loadData($config_id);
        
        return $config;
    }
}