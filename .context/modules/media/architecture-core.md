# Media - Core Architecture (Domain & Writer)
_SOURCE: Application_Media, Application_Media_Document, Document type classes, MediaCollection, MediaRecord, MediaFilterCriteria, MediaFileContent, MediaFileValidator, MediaDocumentWriter, MediaWriterException_
# Application_Media, Application_Media_Document, Document type classes, MediaCollection, MediaRecord, MediaFilterCriteria, MediaFileContent, MediaFileValidator, MediaDocumentWriter, MediaWriterException
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Media/
                └── Collection/
                    ├── MediaCollection.php
                    ├── MediaFilterCriteria.php
                    ├── MediaFilterSettings.php
                    ├── MediaRecord.php
                    ├── MediaSettingsManager.php
                └── Configuration.php
                └── Container.php
                └── Delivery.php
                └── Delivery/
                    ├── MediaResponseHeaders.php
                └── Document.php
                └── Document/
                    ├── Image.php
                    ├── PDF.php
                └── DocumentInterface.php
                └── DocumentTrait.php
                └── ImageDocumentInterface.php
                └── ImageDocumentTrait.php
                └── Media.php
                └── MediaException.php
                └── MediaRightsInterface.php
                └── MediaRightsTrait.php
                └── MediaTagConnector.php
                └── Processor.php
                └── ThumbnailRenderer.php
                └── Writer/
                    └── MediaDocumentWriter.php
                    └── MediaFileContent.php
                    └── MediaFileValidator.php
                    └── MediaWriterException.php

```
###  Path: `/src/classes/Application/Media/Collection/MediaCollection.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Media\Admin\MediaAdminURLs as MediaAdminURLs;
use Application\Media\Admin\Screens\MediaLibraryArea as MediaLibraryArea;
use Application\Media\Events\RegisterMediaTagsListener as RegisterMediaTagsListener;
use Application\Media\MediaTagConnector as MediaTagConnector;
use Application\Tags\TagCollection as TagCollection;
use Application\Tags\TagRecord as TagRecord;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TagCollectionTrait as TagCollectionTrait;
use Application_Formable as Application_Formable;
use Application_Media as Application_Media;
use DBHelper as DBHelper;
use DBHelper_BaseCollection as DBHelper_BaseCollection;

/**
 * @method MediaRecord getByID(int $id)
 * @method MediaFilterCriteria getFilterCriteria()
 * @method MediaFilterSettings getFilterSettings()
 */
class MediaCollection extends DBHelper_BaseCollection implements TagCollectionInterface
{
	use TagCollectionTrait;

	public const COLLECTION_ID = 'media';
	public const RECENT_ITEMS_CATEGORY = 'recent_media';
	public const TABLE_NAME = 'media';
	public const PRIMARY_NAME = 'media_id';
	public const MEDIA_TYPE = 'media';
	public const COL_USER_ID = 'user_id';
	public const COL_DATE_ADDED = 'media_date_added';
	public const COL_TYPE = 'media_type';
	public const COL_NAME = 'media_name';
	public const COL_EXTENSION = 'media_extension';
	public const COL_SIZE = 'file_size';
	public const COL_KEYWORDS = 'keywords';
	public const COL_DESCRIPTION = 'description';

	public function getCollectionID(): string
	{
		/* ... */
	}


	public function getCollectionRegistrationClass(): string
	{
		/* ... */
	}


	/**
	 * The folder that the API method index discovers this module's API
	 * method classes from. Registered in {@see \Application\API\APIFoldersManager::register()}.
	 *
	 * @return FolderInfo
	 */
	public static function getAPIMethodsFolder(): FolderInfo
	{
		/* ... */
	}


	public static function hasSizeColumn(): bool
	{
		/* ... */
	}


	public function getRecordClassName(): string
	{
		/* ... */
	}


	public function getRecordFiltersClassName(): string
	{
		/* ... */
	}


	public function getRecordFilterSettingsClassName(): string
	{
		/* ... */
	}


	public function getRecordDefaultSortKey(): string
	{
		/* ... */
	}


	public function getRecordSearchableColumns(): array
	{
		/* ... */
	}


	public function getRecordTableName(): string
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function getCollectionLabel(): string
	{
		/* ... */
	}


	public function getRecordLabel(): string
	{
		/* ... */
	}


	public function getRecordProperties(): array
	{
		/* ... */
	}


	public function adminURL(): MediaAdminURLs
	{
		/* ... */
	}


	public function getAdminURL(array $params = []): string
	{
		/* ... */
	}


	public static function createSettingsManager(
		Application_Formable $formable,
		?MediaRecord $record = null,
	): MediaSettingsManager
	{
		/* ... */
	}


	public function isTaggingEnabled(): bool
	{
		/* ... */
	}


	public function updateFileSizes(): void
	{
		/* ... */
	}


	public function getTaggableTypeLabel(): string
	{
		/* ... */
	}


	public function getTaggableByID(int $id): MediaRecord
	{
		/* ... */
	}


	public function getTagConnectorClass(): ?string
	{
		/* ... */
	}


	public function getTagPrimary(): string
	{
		/* ... */
	}


	public function getTagTable(): string
	{
		/* ... */
	}


	public function getTagSourceTable(): string
	{
		/* ... */
	}


	public function getRootTag(): ?TagRecord
	{
		/* ... */
	}


	public function getTagRegistryKey(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaCollection.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Microtime as Microtime;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Media\Admin\MediaAdminURLs as MediaAdminURLs;
use Application\Media\Admin\Screens\MediaLibraryArea as MediaLibraryArea;
use Application\Media\Events\RegisterMediaTagsListener as RegisterMediaTagsListener;
use Application\Media\MediaTagConnector as MediaTagConnector;
use Application\Tags\TagCollection as TagCollection;
use Application\Tags\TagRecord as TagRecord;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TagCollectionTrait as TagCollectionTrait;
use Application_Formable as Application_Formable;
use Application_Media as Application_Media;
use DBHelper as DBHelper;
use DBHelper_BaseCollection as DBHelper_BaseCollection;

/**
 * @method MediaRecord getByID(int $id)
 * @method MediaFilterCriteria getFilterCriteria()
 * @method MediaFilterSettings getFilterSettings()
 */
class MediaCollection extends DBHelper_BaseCollection implements TagCollectionInterface
{
	use TagCollectionTrait;

	public const COLLECTION_ID = 'media';
	public const RECENT_ITEMS_CATEGORY = 'recent_media';
	public const TABLE_NAME = 'media';
	public const PRIMARY_NAME = 'media_id';
	public const MEDIA_TYPE = 'media';
	public const COL_USER_ID = 'user_id';
	public const COL_DATE_ADDED = 'media_date_added';
	public const COL_TYPE = 'media_type';
	public const COL_NAME = 'media_name';
	public const COL_EXTENSION = 'media_extension';
	public const COL_SIZE = 'file_size';
	public const COL_KEYWORDS = 'keywords';
	public const COL_DESCRIPTION = 'description';

	public function getCollectionID(): string
	{
		/* ... */
	}


	public function getCollectionRegistrationClass(): string
	{
		/* ... */
	}


	/**
	 * The folder that the API method index discovers this module's API
	 * method classes from. Registered in {@see \Application\API\APIFoldersManager::register()}.
	 *
	 * @return FolderInfo
	 */
	public static function getAPIMethodsFolder(): FolderInfo
	{
		/* ... */
	}


	public static function hasSizeColumn(): bool
	{
		/* ... */
	}


	public function getRecordClassName(): string
	{
		/* ... */
	}


	public function getRecordFiltersClassName(): string
	{
		/* ... */
	}


	public function getRecordFilterSettingsClassName(): string
	{
		/* ... */
	}


	public function getRecordDefaultSortKey(): string
	{
		/* ... */
	}


	public function getRecordSearchableColumns(): array
	{
		/* ... */
	}


	public function getRecordTableName(): string
	{
		/* ... */
	}


	public function getRecordPrimaryName(): string
	{
		/* ... */
	}


	public function getRecordTypeName(): string
	{
		/* ... */
	}


	public function getCollectionLabel(): string
	{
		/* ... */
	}


	public function getRecordLabel(): string
	{
		/* ... */
	}


	public function getRecordProperties(): array
	{
		/* ... */
	}


	public function adminURL(): MediaAdminURLs
	{
		/* ... */
	}


	public function getAdminURL(array $params = []): string
	{
		/* ... */
	}


	public static function createSettingsManager(
		Application_Formable $formable,
		?MediaRecord $record = null,
	): MediaSettingsManager
	{
		/* ... */
	}


	public function isTaggingEnabled(): bool
	{
		/* ... */
	}


	public function updateFileSizes(): void
	{
		/* ... */
	}


	public function getTaggableTypeLabel(): string
	{
		/* ... */
	}


	public function getTaggableByID(int $id): MediaRecord
	{
		/* ... */
	}


	public function getTagConnectorClass(): ?string
	{
		/* ... */
	}


	public function getTagPrimary(): string
	{
		/* ... */
	}


	public function getTagTable(): string
	{
		/* ... */
	}


	public function getTagSourceTable(): string
	{
		/* ... */
	}


	public function getRootTag(): ?TagRecord
	{
		/* ... */
	}


	public function getTagRegistryKey(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaFilterCriteria.php`

```php
namespace Application\Media\Collection;

use Application\AppFactory as AppFactory;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface as TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaTrait as TaggableFilterCriteriaTrait;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use DBHelper_BaseFilterCriteria as DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method MediaRecord[] getItemsObjects()
 * @property MediaCollection $collection
 */
class MediaFilterCriteria extends DBHelper_BaseFilterCriteria implements TaggableFilterCriteriaInterface
{
	use TaggableFilterCriteriaTrait;

	public const FILTER_EXTENSIONS = 'extensions';
	public const FILTER_TYPES = 'types';

	public function selectExtensions(array $extensions): self
	{
		/* ... */
	}


	public function selectExtension(string $extension): self
	{
		/* ... */
	}


	/**
	 * @param string[] $types Media type IDs (e.g. `Image`, `PDF`).
	 * @return $this
	 */
	public function selectMediaTypes(array $types): self
	{
		/* ... */
	}


	/**
	 * @param string $type A media type ID (e.g. `Image`, `PDF`).
	 * @return $this
	 */
	public function selectMediaType(string $type): self
	{
		/* ... */
	}


	public function getColPrimary(): string
	{
		/* ... */
	}


	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaFilterCriteria.php`

```php
namespace Application\Media\Collection;

use Application\AppFactory as AppFactory;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface as TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaTrait as TaggableFilterCriteriaTrait;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use DBHelper_BaseFilterCriteria as DBHelper_BaseFilterCriteria;
use DBHelper_StatementBuilder_ValuesContainer as DBHelper_StatementBuilder_ValuesContainer;

/**
 * @method MediaRecord[] getItemsObjects()
 * @property MediaCollection $collection
 */
class MediaFilterCriteria extends DBHelper_BaseFilterCriteria implements TaggableFilterCriteriaInterface
{
	use TaggableFilterCriteriaTrait;

	public const FILTER_EXTENSIONS = 'extensions';
	public const FILTER_TYPES = 'types';

	public function selectExtensions(array $extensions): self
	{
		/* ... */
	}


	public function selectExtension(string $extension): self
	{
		/* ... */
	}


	/**
	 * @param string[] $types Media type IDs (e.g. `Image`, `PDF`).
	 * @return $this
	 */
	public function selectMediaTypes(array $types): self
	{
		/* ... */
	}


	/**
	 * @param string $type A media type ID (e.g. `Image`, `PDF`).
	 * @return $this
	 */
	public function selectMediaType(string $type): self
	{
		/* ... */
	}


	public function getColPrimary(): string
	{
		/* ... */
	}


	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaFilterSettings.php`

```php
namespace Application\Media\Collection;

use Application\AppFactory as AppFactory;
use Application\Tags\Interfaces\TagFilterSettingsInterface as TagFilterSettingsInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface as TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Traits\TagFilterSettingsTrait as TagFilterSettingsTrait;
use Application_Media_Document_Image as Application_Media_Document_Image;
use Closure as Closure;
use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

/**
 * @property MediaFilterCriteria $filters
 * @property MediaCollection $collection
 */
class MediaFilterSettings extends DBHelper_BaseFilterSettings implements TagFilterSettingsInterface
{
	use TagFilterSettingsTrait;

	public const SETTING_SEARCH = 'search';
	public const SETTING_EXTENSIONS = 'extensions';
	public const SETTING_TAGS = 'tags';

	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}


	public function configureForImageGallery(): self
	{
		/* ... */
	}


	public function getTaggableFilters(): TaggableFilterCriteriaInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaFilterSettings.php`

```php
namespace Application\Media\Collection;

use Application\AppFactory as AppFactory;
use Application\Tags\Interfaces\TagFilterSettingsInterface as TagFilterSettingsInterface;
use Application\Tags\Taggables\FilterCriteria\TaggableFilterCriteriaInterface as TaggableFilterCriteriaInterface;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Traits\TagFilterSettingsTrait as TagFilterSettingsTrait;
use Application_Media_Document_Image as Application_Media_Document_Image;
use Closure as Closure;
use DBHelper_BaseFilterSettings as DBHelper_BaseFilterSettings;

/**
 * @property MediaFilterCriteria $filters
 * @property MediaCollection $collection
 */
class MediaFilterSettings extends DBHelper_BaseFilterSettings implements TagFilterSettingsInterface
{
	use TagFilterSettingsTrait;

	public const SETTING_SEARCH = 'search';
	public const SETTING_EXTENSIONS = 'extensions';
	public const SETTING_TAGS = 'tags';

	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}


	public function configureForImageGallery(): self
	{
		/* ... */
	}


	public function getTaggableFilters(): TaggableFilterCriteriaInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaRecord.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper as FileHelper;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Admin\MediaRecordAdminURLs as MediaRecordAdminURLs;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use Application\Tags\Taggables\TaggableTrait as TaggableTrait;
use Application_Media_Document as Application_Media_Document;
use Application_User as Application_User;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_BaseRecord as DBHelper_BaseRecord;
use DateTime as DateTime;
use UI\AdminURLs\AdminURL as AdminURL;

/**
 * @property MediaCollection $collection
 * @method MediaCollection getCollection()
 */
class MediaRecord extends DBHelper_BaseRecord implements TaggableInterface
{
	use TaggableTrait;

	public function getLabel(): string
	{
		/* ... */
	}


	public function getLabelLinked(): string
	{
		/* ... */
	}


	public function adminURL(): MediaRecordAdminURLs
	{
		/* ... */
	}


	public function adminURLTagging(): AdminURL
	{
		/* ... */
	}


	public function getDateAdded(): DateTime
	{
		/* ... */
	}


	public function getAuthor(): Application_User
	{
		/* ... */
	}


	public function getAuthorID(): int
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getKeywords(): string
	{
		/* ... */
	}


	public function isEditable(): bool
	{
		/* ... */
	}


	/**
	 * NOTE: This is not the same as the media document's
	 * {@see \Application_Media_Document::getFilesize()}
	 * method. It uses the database column value first,
	 * then the file on disk.
	 *
	 * @return int
	 * @throws \Application\Disposables\DisposableDisposedException
	 */
	public function getFileSize(): int
	{
		/* ... */
	}


	/**
	 * @param bool $forceDownload
	 * @return never
	 */
	public function sendFile(bool $forceDownload = false)
	{
		/* ... */
	}


	public function getMediaDocument(): Application_Media_Document
	{
		/* ... */
	}


	public function refreshFileSize(): self
	{
		/* ... */
	}


	public function renderThumbnail(?int $preferredSize = null): string
	{
		/* ... */
	}


	public function getTaggableLabel(): string
	{
		/* ... */
	}


	public function getTagRecordPrimaryValue(): int
	{
		/* ... */
	}


	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}


	public function isTaggingEnabled(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaRecord.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper as FileHelper;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Admin\MediaRecordAdminURLs as MediaRecordAdminURLs;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use Application\Tags\Taggables\TaggableTrait as TaggableTrait;
use Application_Media_Document as Application_Media_Document;
use Application_User as Application_User;
use DBHelper_BaseCollection_OperationContext_Delete as DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_BaseRecord as DBHelper_BaseRecord;
use DateTime as DateTime;
use UI\AdminURLs\AdminURL as AdminURL;

/**
 * @property MediaCollection $collection
 * @method MediaCollection getCollection()
 */
class MediaRecord extends DBHelper_BaseRecord implements TaggableInterface
{
	use TaggableTrait;

	public function getLabel(): string
	{
		/* ... */
	}


	public function getLabelLinked(): string
	{
		/* ... */
	}


	public function adminURL(): MediaRecordAdminURLs
	{
		/* ... */
	}


	public function adminURLTagging(): AdminURL
	{
		/* ... */
	}


	public function getDateAdded(): DateTime
	{
		/* ... */
	}


	public function getAuthor(): Application_User
	{
		/* ... */
	}


	public function getAuthorID(): int
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getKeywords(): string
	{
		/* ... */
	}


	public function isEditable(): bool
	{
		/* ... */
	}


	/**
	 * NOTE: This is not the same as the media document's
	 * {@see \Application_Media_Document::getFilesize()}
	 * method. It uses the database column value first,
	 * then the file on disk.
	 *
	 * @return int
	 * @throws \Application\Disposables\DisposableDisposedException
	 */
	public function getFileSize(): int
	{
		/* ... */
	}


	/**
	 * @param bool $forceDownload
	 * @return never
	 */
	public function sendFile(bool $forceDownload = false)
	{
		/* ... */
	}


	public function getMediaDocument(): Application_Media_Document
	{
		/* ... */
	}


	public function refreshFileSize(): self
	{
		/* ... */
	}


	public function renderThumbnail(?int $preferredSize = null): string
	{
		/* ... */
	}


	public function getTaggableLabel(): string
	{
		/* ... */
	}


	public function getTagRecordPrimaryValue(): int
	{
		/* ... */
	}


	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}


	public function isTaggingEnabled(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaSettingsManager.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\MediaException as MediaException;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use Application_Media_Document as Application_Media_Document;
use Closure as Closure;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Element_InputFile as HTML_QuickForm2_Element_InputFile;
use HTML_QuickForm2_Element_InputText as HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Textarea as HTML_QuickForm2_Element_Textarea;
use HTML_QuickForm2_Rule_Callback as HTML_QuickForm2_Rule_Callback;
use UI as UI;

/**
 * @property MediaRecord|null $record
 */
class MediaSettingsManager extends Application_Formable_RecordSettings_Extended
{
	public const ERROR_NO_DOCUMENT_IN_CREATE_MODE = 146101;
	public const SETTING_NAME = 'name';
	public const SETTING_FILE = 'file';
	public const SETTING_DESCRIPTION = 'description';
	public const SETTING_KEYWORDS = 'keywords';

	public function getDefaultSettingName(): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Collection/MediaSettingsManager.php`

```php
namespace Application\Media\Collection;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\MediaException as MediaException;
use Application_Formable_RecordSettings_Extended as Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet as Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable as Application_Interfaces_Formable;
use Application_Media_Document as Application_Media_Document;
use Closure as Closure;
use DBHelper\Interfaces\DBHelperRecordInterface as DBHelperRecordInterface;
use HTML_QuickForm2_Element_InputFile as HTML_QuickForm2_Element_InputFile;
use HTML_QuickForm2_Element_InputText as HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Textarea as HTML_QuickForm2_Element_Textarea;
use HTML_QuickForm2_Rule_Callback as HTML_QuickForm2_Rule_Callback;
use UI as UI;

/**
 * @property MediaRecord|null $record
 */
class MediaSettingsManager extends Application_Formable_RecordSettings_Extended
{
	public const ERROR_NO_DOCUMENT_IN_CREATE_MODE = 146101;
	public const SETTING_NAME = 'name';
	public const SETTING_FILE = 'file';
	public const SETTING_DESCRIPTION = 'description';
	public const SETTING_KEYWORDS = 'keywords';

	public function getDefaultSettingName(): string
	{
		/* ... */
	}


	public function isUserAllowedEditing(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Configuration.php`

```php
namespace ;

use Application\Application as Application;

abstract class Application_Media_Configuration
{
	public const ERROR_UNKNOWN_MEDIA_CONFIGURATION = 690001;
	public const ERROR_DATASET_TYPE_MISMATCH = 690002;
	public const ERROR_INVALID_DOCUMENT_TYPE = 690003;

	public function getTypeID()
	{
		/* ... */
	}


	public function getID()
	{
		/* ... */
	}


	/**
	 * Checks whether the specified document has to be pre-processed.
	 * @param Application_Media_Document $document
	 */
	abstract public function isProcessRequired(Application_Media_Document $document);


	/**
	 * Makes the configuration load a stored configuration from
	 * the database. The document type has to match of course.
	 *
	 * @param integer $config_id
	 * @throws Application_Exception
	 */
	public function loadData($config_id)
	{
		/* ... */
	}


	/**
	 * Processes the media document using the configuration:
	 * goes through all required pre-processing steps required
	 * by the document type. For images for example, this will
	 * create all resized versions for the available presets.
	 *
	 * @param Application_Media_Document $document
	 */
	abstract public function process(Application_Media_Document $document);
}


```
###  Path: `/src/classes/Application/Media/Container.php`

```php
namespace ;

/**
 * Interface for media containers. Media containers can describe
 * their media content to allow for automatic media processing
 * using the media processor class. This is mainly used for automatic
 * image preprocessing according to image size presets.
 *
 * @package Application
 * @subpackage Media
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Media_Processor
 * @see Application_Media_Processor::addContainer()
 */
interface Application_Media_Container
{
	/**
	 * Retrieves a list of media documents stored within this
	 * media container, as an indexed array with document instances.
	 *
	 * NOTE: Each document must have its media configuration set
	 * so it can be processed properly.
	 *
	 * @return Application_Media_Document[]
	 */
	public function getMediaDocuments();


	/**
	 * Checks whether all available media documents have been
	 * properly prepared/processed, and as such can be considered
	 * complete.
	 *
	 * @return boolean
	 */
	public function isMediaPrepared();
}


```
###  Path: `/src/classes/Application/Media/Delivery.php`

```php
namespace ;

use Application\Application as Application;
use Application\Media\Collection\MediaCollection as MediaCollection;

class Application_Media_Delivery implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	/**
	 * Retrieves the global instance of the media manager. Creates
	 * the instance as needed.
	 *
	 * @return Application_Media_Delivery
	 */
	public static function getInstance(): Application_Media_Delivery
	{
		/* ... */
	}


	/**
	 * Serves a media file from the current request: checks
	 * the request variables for the required parameters and
	 * retrieves the matching media file, then serves it.
	 */
	public function serveFromRequest(): void
	{
		/* ... */
	}


	public function sendError(string $title): void
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Delivery/MediaResponseHeaders.php`

```php
namespace Application\Media\Delivery;

/**
 * Response-header policy for files served through {@see \Application\Media\DocumentTrait}'s
 * shared serving seam ({@see \Application\Media\DocumentTrait::serveFromRequest()} and
 * {@see \Application\Media\DocumentTrait::sendFile()}), used by both media documents and
 * uploads.
 *
 * Every served file gets `X-Content-Type-Options: nosniff`, preventing a browser from
 * MIME-sniffing a served file into a more dangerous content type than the one it was
 * stored and served as. A file extension in {@see self::ACTIVE_CONTENT_EXTENSIONS}
 * (currently SVG/SVGZ, which can carry inline `<script>` content executed by a browser
 * when the file is opened directly — never when embedded via `<img>`) additionally gets
 * a sandboxing Content-Security-Policy, neutralising that script without altering the
 * served file's bytes.
 *
 * @package Application
 * @subpackage Media
 */
class MediaResponseHeaders
{
	/**
	 * File extensions (lowercase) that may carry active content (e.g. inline
	 * `<script>`) a browser could execute when the file is opened directly.
	 *
	 * @var string[]
	 */
	public const ACTIVE_CONTENT_EXTENSIONS = ['svg', 'svgz'];

	/**
	 * The Content-Security-Policy applied to a served file whose extension is
	 * in {@see self::ACTIVE_CONTENT_EXTENSIONS}: disallows every resource type
	 * by default, allows only inline styles (needed by legitimate SVG styling),
	 * and sandboxes the document — which disables script execution, among
	 * other restrictions — without touching the file's own content.
	 */
	public const ACTIVE_CONTENT_CSP = 'default-src \'none\'; style-src \'unsafe-inline\'; sandbox';

	/**
	 * Builds the response headers to send for a file with the given
	 * extension, without sending them.
	 *
	 * @param string $extension The extension of the file actually being sent
	 *        (the thumbnail/original path's extension), matched
	 *        case-insensitively.
	 * @return array<string,string> Header name => value.
	 */
	public static function forExtension(string $extension): array
	{
		/* ... */
	}


	/**
	 * Sends the response headers for a file with the given extension (see
	 * {@see self::forExtension()}), unless headers have already been sent.
	 *
	 * @param string $extension The extension of the file actually being sent.
	 * @param bool|null $headersSent Overrides the `headers_sent()` check used
	 *        to decide whether sending is still possible. Left at `null` in
	 *        production, where the real global state is used. Unit tests
	 *        pass an explicit value instead of relying on `headers_sent()`
	 *        itself, since PHPUnit's own output buffering keeps it `false`
	 *        regardless of echoed test output under the CLI SAPI — the same
	 *        kind of untestable global state
	 *        {@see \Application\ConfigSettings\AppConfig::deriveAPIMaxRequestBodySize()}
	 *        works around for boot constants.
	 * @return bool `true` if the headers were sent, `false` if headers were
	 *        already sent and nothing was emitted.
	 */
	public static function send(string $extension, ?bool $headersSent = null): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Document.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\ConvertHelper_Exception as ConvertHelper_Exception;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use AppUtils\Microtime as Microtime;
use AppUtils\Microtime_Exception as Microtime_Exception;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application\Media\DocumentTrait as DocumentTrait;
use Application\Media\MediaException as MediaException;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TaggableInterface as TaggableInterface;
use Application\Tags\Taggables\TaggableTrait as TaggableTrait;
use UI\AdminURLs\AdminURL as AdminURL;

abstract class Application_Media_Document implements Application_Media_DocumentInterface, TaggableInterface
{
	use Application_Traits_Loggable;
	use DocumentTrait;
	use TaggableTrait;

	public const ERROR_CONFIGURATION_TYPE_MISMATCH = 650001;
	public const ERROR_CANNOT_CHECK_PROCESSING_REQUIREMENTS = 650002;
	public const ERROR_NO_TRANSACTION_STARTED = 650003;
	public const ERROR_FILE_NOT_FOUND = 650004;
	public const ERROR_DATA_NOT_FOUND = 650005;

	public function getID(): int
	{
		/* ... */
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
	public static function createNew(
		string $name,
		string $extension,
		?Application_User $user = null,
		?DateTime $date_added = null,
	): Application_Media_Document
	{
		/* ... */
	}


	/**
	 * @return string
	 * @throws Microtime_Exception
	 */
	public function getPath(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the date that this document was added.
	 * @return DateTime
	 * @throws Microtime_Exception
	 */
	public function getDateAdded(): DateTime
	{
		/* ... */
	}


	/**
	 * Retrieves the document's extension. e.g. "jpg".
	 * @return string
	 */
	public function getExtension(): string
	{
		/* ... */
	}


	public function getName(): string
	{
		/* ... */
	}


	public function getUserID(): int
	{
		/* ... */
	}


	public static function createNewFromFile(
		string $name,
		FileInfo $file,
		?Application_User $user = null,
		?DateTime $dateAdded = null,
	): Application_Media_Document
	{
		/* ... */
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
	public static function createNewFromUpload(Application_Uploads_Upload $upload): Application_Media_Document
	{
		/* ... */
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
	public static function create(int $media_id): Application_Media_Document
	{
		/* ... */
	}


	/**
	 * Human-readable label for this media type, e.g. "Image".
	 * Must be implemented by the media type class.
	 */
	abstract public static function getLabel(): string;


	abstract public static function getIcon(): UI_Icon;


	/**
	 * List of supported file extensions for this media file type.
	 * Must be implemented by the media type class.
	 *
	 * @return string[]
	 */
	abstract public static function getExtensions(): array;


	/**
	 * Checks whether the given file content is a genuine, well-formed file
	 * of this media type for the given extension — e.g. for the `Image`
	 * type, that `$content` is really an image whose detected format
	 * matches the extension's family (jpg/jpeg → JPEG, png → PNG,
	 * gif → GIF), or a well-formed SVG document.
	 *
	 * Must be implemented by the media type class, purely in memory: no
	 * filesystem access is allowed, so the check can run during a dry run
	 * and on content that has not been (and may never be) written to disk.
	 *
	 * @param string $content The raw file content to check.
	 * @param string $extension The file extension the content is claimed
	 *        to have (lowercase, without the leading dot), one of
	 *        {@see self::getExtensions()}.
	 * @return bool
	 */
	abstract public static function isContentValid(string $content, string $extension): bool;


	/**
	 * Retrieves an identification string for the media document that
	 * is mainly used for logging purposes. Contains the document's ID
	 * and filename.
	 *
	 * @return string
	 */
	public function getIdentification(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the ID of the document type, e.g. <code>Image</code>.
	 * @return string
	 */
	public function getTypeID(): string
	{
		/* ... */
	}


	/**
	 * Sets the configuration for this media document,
	 * when it is being pre-processed using the media processor.
	 *
	 * @param Application_Media_Configuration $config
	 * @throws MediaException
	 */
	public function setConfiguration(Application_Media_Configuration $config): void
	{
		/* ... */
	}


	/**
	 * Checks whether a media configuration has been set for the document.
	 * @return boolean
	 */
	public function hasConfiguration(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the document's media configuration.
	 *
	 * NOTE: this is not always available.
	 *
	 * @return Application_Media_Configuration|NULL
	 */
	public function getConfiguration(): ?Application_Media_Configuration
	{
		/* ... */
	}


	/**
	 * Checks whether this media document has to be pre-processed.
	 *
	 * @throws MediaException
	 * @return boolean
	 */
	public function isProcessRequired(): bool
	{
		/* ... */
	}


	public function isUpload(): bool
	{
		/* ... */
	}


	public function upgrade(): Application_Media_Document
	{
		/* ... */
	}


	/**
	 * Retrieves the form value to use for the document.
	 * @return array{name:string,state:string,id:int}
	 */
	public function toFormValue(): array
	{
		/* ... */
	}


	/**
	 * Encodes the image's binary data to base64.
	 * @return string|NULL
	 *
	 * @throws MediaException {@see self::ERROR_FILE_NOT_FOUND}
	 */
	public function toBase64(): ?string
	{
		/* ... */
	}


	/**
	 * @return void
	 * @throws ConvertHelper_Exception
	 * @throws DBHelper_Exception
	 * @throws JsonException
	 * @throws MediaException
	 */
	public function delete(): void
	{
		/* ... */
	}


	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * Sets/overwrites the document from the specified file.
	 *
	 * @param FileInfo $file
	 * @return $this
	 * @throws FileHelper_Exception
	 */
	public function setSourceFile(FileInfo $file): self
	{
		/* ... */
	}


	public function getFileInfo(): FileInfo
	{
		/* ... */
	}


	public function getTypeIcon(): UI_Icon
	{
		/* ... */
	}


	public function getTypeLabel(): string
	{
		/* ... */
	}


	abstract public function injectMetadata(UI_PropertiesGrid $grid): void;


	public function getTaggableLabel(): string
	{
		/* ... */
	}


	public function getTagCollection(): TagCollectionInterface
	{
		/* ... */
	}


	public function getTagRecordPrimaryValue(): int
	{
		/* ... */
	}


	public function adminURLTagging(): AdminURL
	{
		/* ... */
	}


	public function isTaggingEnabled(): bool
	{
		/* ... */
	}


	public function getRecord(): MediaRecord
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Document/Image.php`

```php
namespace ;

use AppUtils\BaseException as BaseException;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage as GIFImage;
use AppUtils\ImageHelper_Size as ImageHelper_Size;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\ImageDocumentInterface as ImageDocumentInterface;
use Application\Media\ImageDocumentTrait as ImageDocumentTrait;
use Application\Media\MediaException as MediaException;

class Application_Media_Document_Image extends Application_Media_Document implements ImageDocumentInterface
{
	use ImageDocumentTrait;

	public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

	public static function getLabel(): string
	{
		/* ... */
	}


	public static function getIcon(): UI_Icon
	{
		/* ... */
	}


	public static function getExtensions(): array
	{
		/* ... */
	}


	/**
	 * Checks that the content is a genuine image file matching the given
	 * extension's family: a raster extension (jpg/jpeg/png/gif) must decode
	 * to the matching `IMAGETYPE_*` via {@see getimagesizefromstring()}; the
	 * `svg` extension must be a well-formed XML document whose first
	 * element is `svg`. This is a file-type check only, not a content
	 * policy — e.g. SVG scripts and external references are not inspected
	 * here.
	 *
	 * @param string $content
	 * @param string $extension
	 * @return bool
	 */
	public static function isContentValid(string $content, string $extension): bool
	{
		/* ... */
	}


	/**
	 * Retrieves an instance of the image helper for this image
	 * that can be used to do any number of operations on the
	 * source image.
	 *
	 * @return ImageHelper
	 */
	public function getImageHelper(): ImageHelper
	{
		/* ... */
	}


	/**
	 * Calculates the image dimensions for the target width,
	 * keeping the aspect ratio.
	 *
	 * @param integer $width
	 * @return ImageHelper_Size
	 */
	public function getSizeByWidth(int $width): ImageHelper_Size
	{
		/* ... */
	}


	public function exists(): bool
	{
		/* ... */
	}


	public function getMediaSourceID(): string
	{
		/* ... */
	}


	public function getMediaPrimaryName(): string
	{
		/* ... */
	}


	/**
	 * @return int
	 * @throws BaseException
	 * @throws MediaException
	 */
	public function getWidth(): int
	{
		/* ... */
	}


	/**
	 * @return int
	 * @throws BaseException
	 * @throws MediaException
	 */
	public function getHeight(): int
	{
		/* ... */
	}


	/**
	 * @return ImageHelper_Size
	 * @throws MediaException
	 * @throws BaseException
	 */
	public function getDimensions(): ImageHelper_Size
	{
		/* ... */
	}


	public function injectMetadata(UI_PropertiesGrid $grid): void
	{
		/* ... */
	}


	public function isAnimatedGIF(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Document/Image.php`

```php
namespace ;

use AppUtils\BaseException as BaseException;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage as GIFImage;
use AppUtils\ImageHelper_Size as ImageHelper_Size;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\ImageDocumentInterface as ImageDocumentInterface;
use Application\Media\ImageDocumentTrait as ImageDocumentTrait;
use Application\Media\MediaException as MediaException;

class Application_Media_Document_Image extends Application_Media_Document implements ImageDocumentInterface
{
	use ImageDocumentTrait;

	public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

	public static function getLabel(): string
	{
		/* ... */
	}


	public static function getIcon(): UI_Icon
	{
		/* ... */
	}


	public static function getExtensions(): array
	{
		/* ... */
	}


	/**
	 * Checks that the content is a genuine image file matching the given
	 * extension's family: a raster extension (jpg/jpeg/png/gif) must decode
	 * to the matching `IMAGETYPE_*` via {@see getimagesizefromstring()}; the
	 * `svg` extension must be a well-formed XML document whose first
	 * element is `svg`. This is a file-type check only, not a content
	 * policy — e.g. SVG scripts and external references are not inspected
	 * here.
	 *
	 * @param string $content
	 * @param string $extension
	 * @return bool
	 */
	public static function isContentValid(string $content, string $extension): bool
	{
		/* ... */
	}


	/**
	 * Retrieves an instance of the image helper for this image
	 * that can be used to do any number of operations on the
	 * source image.
	 *
	 * @return ImageHelper
	 */
	public function getImageHelper(): ImageHelper
	{
		/* ... */
	}


	/**
	 * Calculates the image dimensions for the target width,
	 * keeping the aspect ratio.
	 *
	 * @param integer $width
	 * @return ImageHelper_Size
	 */
	public function getSizeByWidth(int $width): ImageHelper_Size
	{
		/* ... */
	}


	public function exists(): bool
	{
		/* ... */
	}


	public function getMediaSourceID(): string
	{
		/* ... */
	}


	public function getMediaPrimaryName(): string
	{
		/* ... */
	}


	/**
	 * @return int
	 * @throws BaseException
	 * @throws MediaException
	 */
	public function getWidth(): int
	{
		/* ... */
	}


	/**
	 * @return int
	 * @throws BaseException
	 * @throws MediaException
	 */
	public function getHeight(): int
	{
		/* ... */
	}


	/**
	 * @return ImageHelper_Size
	 * @throws MediaException
	 * @throws BaseException
	 */
	public function getDimensions(): ImageHelper_Size
	{
		/* ... */
	}


	public function injectMetadata(UI_PropertiesGrid $grid): void
	{
		/* ... */
	}


	public function isAnimatedGIF(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Document/PDF.php`

```php
namespace ;

use Application\Media\Collection\MediaCollection as MediaCollection;

class Application_Media_Document_PDF extends Application_Media_Document
{
	public static function getLabel(): string
	{
		/* ... */
	}


	public static function getIcon(): UI_Icon
	{
		/* ... */
	}


	public static function getExtensions(): array
	{
		/* ... */
	}


	/**
	 * Checks the content starts with the PDF file signature (`%PDF-`).
	 * This is a file-type check only, not a structural validation of the
	 * rest of the document.
	 *
	 * @param string $content
	 * @param string $extension
	 * @return bool
	 */
	public static function isContentValid(string $content, string $extension): bool
	{
		/* ... */
	}


	public function getMaxThumbnailSize(): int
	{
		/* ... */
	}


	public function injectMetadata(UI_PropertiesGrid $grid): void
	{
		/* ... */
	}


	public function getThumbnailSourcePath(): string
	{
		/* ... */
	}


	public function getMediaSourceID(): string
	{
		/* ... */
	}


	public function getMediaPrimaryName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Document/PDF.php`

```php
namespace ;

use Application\Media\Collection\MediaCollection as MediaCollection;

class Application_Media_Document_PDF extends Application_Media_Document
{
	public static function getLabel(): string
	{
		/* ... */
	}


	public static function getIcon(): UI_Icon
	{
		/* ... */
	}


	public static function getExtensions(): array
	{
		/* ... */
	}


	/**
	 * Checks the content starts with the PDF file signature (`%PDF-`).
	 * This is a file-type check only, not a structural validation of the
	 * rest of the document.
	 *
	 * @param string $content
	 * @param string $extension
	 * @return bool
	 */
	public static function isContentValid(string $content, string $extension): bool
	{
		/* ... */
	}


	public function getMaxThumbnailSize(): int
	{
		/* ... */
	}


	public function injectMetadata(UI_PropertiesGrid $grid): void
	{
		/* ... */
	}


	public function getThumbnailSourcePath(): string
	{
		/* ... */
	}


	public function getMediaSourceID(): string
	{
		/* ... */
	}


	public function getMediaPrimaryName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/DocumentInterface.php`

```php
namespace ;

use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper\ImageFormats\ImageFormatInterface as ImageFormatInterface;

interface Application_Media_DocumentInterface extends Application_Interfaces_Loggable
{
	public const DEFAULT_THUMBNAIL_SIZE = 128;
	public const DEFAULT_TYPE_ICON_THUMBNAIL_SIZE = 64;

	/**
	 * Retrieves the ID of the document.
	 * @return int
	 */
	public function getID(): int;


	/**
	 * Whether this document is an uploaded document, which
	 * has not been transformed into a full media document.
	 *
	 * @return boolean
	 */
	public function isUpload(): bool;


	/**
	 * Retrieves the name of the uploaded file, without an extension.
	 * @return string
	 */
	public function getName(): string;


	/**
	 * Retrieves the media document's filename, e.g.:
	 *
	 * <code>media_name.jpg</code>
	 *
	 * Note that this is NOT the filename on disk, but the
	 * filename as defined by the user that should be used
	 * when the media file is downloaded or used in the exports.
	 *
	 * @return string
	 */
	public function getFilename(): string;


	/**
	 * Retrieves the full path to the uploaded file in the temporary storage folder.
	 * @return string
	 */
	public function getPath(): string;


	/**
	 * Gets the image that should be used to generate the
	 * document's thumbnail.
	 *
	 * @return string The absolute path to the file on disk.
	 */
	public function getThumbnailSourcePath(): string;


	public function getThumbnailSourceImage(): ImageHelper;


	/**
	 * Retrieves the full URL to the media script to display a thumbnail
	 * of the media file. The width and height parameters can be set as
	 * needed to resample the thumbnail to the target size.
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @return string
	 */
	public function getThumbnailURL(?int $width = null, ?int $height = null): string;


	/**
	 * Retrieves the default size for thumbnails of this document type.
	 *
	 * @param int|NULL $preferredSize Specify a preferred size, which will be used if possible. Use 0 or NULL to ignore.
	 * @return int
	 */
	public function getThumbnailDefaultSize(?int $preferredSize = null): int;


	/**
	 * Retrieves the maximum size, in pixels, that a thumbnail of this
	 * document may be generated at. Used by {@see self::getThumbnailDefaultSize()}
	 * to cap the preferred size to what the document can actually provide.
	 *
	 * @return int
	 */
	public function getMaxThumbnailSize(): int;


	/**
	 * Renders a thumbnail image HTML tag for the document.
	 *
	 * @param int|null $preferredSize Will be used if the document allows this size. Use 0 or NULL to ignore.
	 * @return string
	 */
	public function renderThumbnail(?int $preferredSize = null): string;


	/**
	 * @return bool
	 * @deprecated Use {@see self::getImageFormat()} instead.
	 */
	public function isTypeSVG(): bool;


	public function getTypeIconPath(): string;


	public function getTypeIconURL(): string;


	public function supportsThumbnails(): bool;


	public function isVector(): bool;


	public function getImageFormat(): ImageFormatInterface;


	/**
	 * Retrieves the extension of the file. Always lowercase.
	 * @return string
	 */
	public function getExtension(): string;


	/**
	 * Retrieves the creation date of the file.
	 * @return DateTime
	 */
	public function getDateAdded(): DateTime;


	/**
	 * Retrieves the size of the file on disk, in bytes.
	 * @return int
	 */
	public function getFilesize(): int;


	public function getMediaSourceID(): string;


	public function getMediaPrimaryName(): string;


	/**
	 * Retrieves a human-readable representation of the file's size.
	 * @return string
	 */
	public function getFilesizeReadable(): string;


	/**
	 * Retrieves the user that created the upload.
	 * @return Application_User
	 */
	public function getUser(): Application_User;


	public function getUserID(): int;


	/**
	 * Creates a thumbnail of the image for the specified dimensions.
	 * Width and height can be omitted as needed to constrain resampling
	 * to one or none of the sides.
	 *
	 * Returns the path to the thumbnail file when successful.
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @return string The path to the generated file.
	 */
	public function createThumbnail(?int $width = null, ?int $height = null): string;


	/**
	 * Serves the media file using a configuration set via the current
	 * request. For image types for example, the width and height of
	 * the image can be set in the request. This is used by the media
	 * delivery script.
	 *
	 * @param Application_Media_Delivery $delivery
	 * @param Application_Request $request
	 * @return never
	 */
	public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request);


	/**
	 * Upgrades the upload to a full media document.
	 * Has no effect if it is already a media document.
	 *
	 * @return Application_Media_Document
	 */
	public function upgrade(): Application_Media_Document;


	/**
	 * Deletes the media document: removes the file(s) on disk
	 * and the database entries. Note that this does not ensure
	 * that any records linking to this file are updated as well:
	 * this can either be done via relations in the database, or
	 * manually correcting those.
	 */
	public function delete(): void;


	/**
	 * @param bool $forceDownload
	 * @return never
	 */
	public function sendFile(bool $forceDownload = false);
}


```
###  Path: `/src/classes/Application/Media/DocumentTrait.php`

```php
namespace Application\Media;

use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use AppUtils\FileHelper_Exception as FileHelper_Exception;
use AppUtils\ImageHelper as ImageHelper;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage as GIFImage;
use AppUtils\ImageHelper\ImageFormats\Formats\SVGImage as SVGImage;
use AppUtils\ImageHelper\ImageFormats\FormatsCollection as FormatsCollection;
use AppUtils\ImageHelper\ImageFormats\ImageFormatInterface as ImageFormatInterface;
use AppUtils\ImageHelper_Exception as ImageHelper_Exception;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Delivery\MediaResponseHeaders as MediaResponseHeaders;
use Application_Exception as Application_Exception;
use Application_Media_Delivery as Application_Media_Delivery;
use Application_Media_Document as Application_Media_Document;
use Application_Media_DocumentInterface as Application_Media_DocumentInterface;
use Application_Request as Application_Request;
use Application_User as Application_User;

trait DocumentTrait
{
	public function getThumbnailSourceImage(): ImageHelper
	{
		/* ... */
	}


	/**
	 * Retrieves the maximum size, in pixels, that a thumbnail of this
	 * document may be generated at. Used by {@see self::getThumbnailDefaultSize()}
	 * to cap the preferred size to what the document can actually provide.
	 *
	 * Must be implemented by the concrete document type, since the
	 * semantics differ per type: a raster image returns its own pixel
	 * width, a PDF returns the type icon's thumbnail size, and so on.
	 *
	 * @return int
	 */
	abstract public function getMaxThumbnailSize(): int;


	public function thumbnailExists(?int $width = null, ?int $height = null): bool
	{
		/* ... */
	}


	public function getThumbnailPath(?int $width = null, ?int $height = null): string
	{
		/* ... */
	}


	/**
	 * Creates a thumbnail of the image for the specified dimensions.
	 * Width and height can be omitted as needed to constrain resampling
	 * to one or none of the sides.
	 *
	 * Returns the path to the thumbnail file when successful.
	 *
	 * @param int|null $width
	 * @param int|null $height
	 * @return string
	 * @throws ImageHelper_Exception
	 */
	public function createThumbnail(?int $width = null, ?int $height = null): string
	{
		/* ... */
	}


	public function getImageFormat(): ImageFormatInterface
	{
		/* ... */
	}


	public function supportsThumbnails(): bool
	{
		/* ... */
	}


	public static function formatSupportsThumbnails(ImageFormatInterface $format, string $filePath): bool
	{
		/* ... */
	}


	/**
	 * @param bool $forceDownload
	 * @return never
	 */
	public function sendFile(bool $forceDownload = false)
	{
		/* ... */
	}


	/**
	 * Renders a thumbnail image HTML tag for the document.
	 *
	 * @param int|null $preferredSize Will be used if the document allows this size. Use 0 or NULL to ignore.
	 * @return string
	 */
	public function renderThumbnail(?int $preferredSize = null): string
	{
		/* ... */
	}


	/**
	 * Retrieves the default size for thumbnails of this document type.
	 *
	 * @param int|NULL $preferredSize Specify a preferred size, which will be used if possible. Use 0 or NULL to ignore.
	 * @return int
	 */
	public function getThumbnailDefaultSize(?int $preferredSize = null): int
	{
		/* ... */
	}


	/**
	 * Retrieves the size of the media file on disk, in bytes.
	 * @return int
	 */
	public function getFilesize(): int
	{
		/* ... */
	}


	/**
	 * @return bool
	 * @deprecated Use {@see self::getImageFormat()} instead.
	 */
	public function isTypeSVG(): bool
	{
		/* ... */
	}


	public function isVector(): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the user that created the upload.
	 * @return Application_User
	 * @throws Application_Exception
	 */
	public function getUser(): Application_User
	{
		/* ... */
	}


	/**
	 * Retrieves the size of the media file in a human-readable format,
	 * e.g. 15 Kb.
	 *
	 * @return string
	 */
	public function getFilesizeReadable(): string
	{
		/* ... */
	}


	public function getFilename(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the full URL to the media script to display a thumbnail
	 * of the media file. The width and height parameters can be set as
	 * needed to resample the thumbnail to the target size.
	 *
	 * @param integer|NULL $width
	 * @param integer|NULL $height
	 * @return string
	 */
	public function getThumbnailURL(?int $width = null, ?int $height = null): string
	{
		/* ... */
	}


	/**
	 * @param Application_Media_Delivery $delivery
	 * @param Application_Request $request
	 * @return never
	 * @throws FileHelper_Exception
	 */
	public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request)
	{
		/* ... */
	}


	public function getTypeIconURL(): string
	{
		/* ... */
	}


	public function getTypeIconPath(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/ImageDocumentInterface.php`

```php
namespace Application\Media;

use AppUtils\ImageHelper_Size as ImageHelper_Size;
use Application_Media_DocumentInterface as Application_Media_DocumentInterface;

interface ImageDocumentInterface extends Application_Media_DocumentInterface
{
	public function getWidth(): int;


	public function getHeight(): int;


	public function getDimensions(): ImageHelper_Size;
}


```
###  Path: `/src/classes/Application/Media/ImageDocumentTrait.php`

```php
namespace Application\Media;

use AppUtils\BaseException as BaseException;
use AppUtils\Microtime_Exception as Microtime_Exception;

trait ImageDocumentTrait
{
	/**
	 * @return string
	 * @throws Microtime_Exception
	 */
	public function getThumbnailSourcePath(): string
	{
		/* ... */
	}


	/**
	 * @return int
	 * @throws MediaException
	 * @throws BaseException
	 */
	public function getMaxThumbnailSize(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Media.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException as BaseClassHelperException;
use AppUtils\ConvertHelper as ConvertHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaFilterCriteria as MediaFilterCriteria;
use Application\Media\Events\RegisterMediaTagsListener as RegisterMediaTagsListener;
use Application\Media\MediaException as MediaException;
use Application\Media\MediaTagConnector as MediaTagConnector;
use Application\Tags\Taggables\TagCollectionInterface as TagCollectionInterface;
use Application\Tags\Taggables\TagCollectionTrait as TagCollectionTrait;

/**
 * @method MediaTagConnector getTagConnector()
 */
class Application_Media implements TagCollectionInterface
{
	use TagCollectionTrait;

	public const COLLECTION_ID = 'media_legacy';
	public const ERROR_UNKNOWN_MEDIA_CONFIGURATION = 680001;
	public const ERROR_NOT_AN_IMAGE_MEDIA_FILE = 680002;
	public const TABLE_NAME = 'media';
	public const TABLE_NAME_CONFIGURATIONS = 'media_configurations';
	public const TABLE_TAGS = 'media_tags';
	public const PRIMARY_NAME = 'media_id';
	public const TAG_REGISTRY_KEY = 'media_tagging';

	/**
	 * Retrieves the global instance of the media manager. Creates
	 * the instance as needed.
	 *
	 * @return Application_Media
	 */
	public static function getInstance(): Application_Media
	{
		/* ... */
	}


	public function getCollectionRegistrationClass(): string
	{
		/* ... */
	}


	public function getCollectionID(): string
	{
		/* ... */
	}


	/**
	 * Retrieves the full path to the folder where media files are stored.
	 * @return string
	 */
	public function getStorageFolder(): string
	{
		/* ... */
	}


	public function createFromFile(
		string $name,
		FileInfo $file,
		?Application_User $user = null,
		?DateTime $dateAdded = null,
	): Application_Media_Document
	{
		/* ... */
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
	public function createImageFromFile(
		string $name,
		FileInfo $file,
		?Application_User $user = null,
		?DateTime $dateAdded = null,
	): Application_Media_Document_Image
	{
		/* ... */
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
	public function createFromUpload(Application_Uploads_Upload $upload): Application_Media_Document
	{
		/* ... */
	}


	/**
	 * Retrieves a media document by its ID.
	 * Throws an exception if it does not exist in the database.
	 *
	 * @param int $media_id
	 * @return Application_Media_Document
	 */
	public function getByID(int $media_id): Application_Media_Document
	{
		/* ... */
	}


	public function clearCollection(): void
	{
		/* ... */
	}


	/**
	 * Attempts to retrieve a media document from a form value.
	 * @param mixed $value
	 * @return Application_Media_Document|NULL
	 */
	public function getByFormValue($value): ?Application_Media_Document
	{
		/* ... */
	}


	/**
	 * @param mixed $value
	 * @return bool
	 */
	public function isMediaFormValue($value): bool
	{
		/* ... */
	}


	/**
	 * Retrieves the media type ID for the specified extension,
	 * or NULL if no types exist to handle that extension.
	 *
	 * @param string $extension
	 * @return NULL|string
	 */
	public function getTypeByExtension(string $extension): ?string
	{
		/* ... */
	}


	/**
	 * Retrieves a list of all supported media file extensions.
	 * @return string[]
	 */
	public function getExtensions(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the IDs of all registered media document types
	 * (e.g. `Image`, `PDF`), sorted alphabetically.
	 *
	 * @return string[]
	 */
	public function getTypeIDs(): array
	{
		/* ... */
	}


	/**
	 * Retrieves the human-readable label of the given media document
	 * type ID, or the type ID itself if it is not a known type.
	 *
	 * @param string $typeID
	 * @return string
	 */
	public function getTypeLabel(string $typeID): string
	{
		/* ... */
	}


	/**
	 * Creates a media configuration instance. These are document
	 * type-specific, and are used to store configurations for
	 * media pre-processing using the media processor class. For
	 * example, they are used to store the size presets to resize
	 * images.
	 *
	 * @param string $type The configuration type, e.g. "Image". Case-sensitive.
	 * @return Application_Media_Configuration
	 *
	 * @throws BaseClassHelperException
	 * @throws Throwable
	 */
	public function createConfiguration(string $type): Application_Media_Configuration
	{
		/* ... */
	}


	/**
	 * @param integer $media_id
	 * @return boolean
	 */
	public function idExists(int $media_id): bool
	{
		/* ... */
	}


	public function configurationIDExists(int $config_id): bool
	{
		/* ... */
	}


	public function getConfigurationByID(int $config_id): Application_Media_Configuration
	{
		/* ... */
	}


	public function getTaggableTypeLabel(): string
	{
		/* ... */
	}


	public function getTaggableByID(int $id): Application_Media_Document
	{
		/* ... */
	}


	public function getTagPrimary(): string
	{
		/* ... */
	}


	public function getTagTable(): string
	{
		/* ... */
	}


	public function getTagConnectorClass(): ?string
	{
		/* ... */
	}


	public function getTagSourceTable(): string
	{
		/* ... */
	}


	public function getTagRegistryKey(): string
	{
		/* ... */
	}


	public function getFilterCriteria(): MediaFilterCriteria
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/MediaException.php`

```php
namespace Application\Media;

use Application_Exception as Application_Exception;

class MediaException extends Application_Exception
{
}


```
###  Path: `/src/classes/Application/Media/MediaRightsInterface.php`

```php
namespace Application\Media;

use Application_User_Interface as Application_User_Interface;

interface MediaRightsInterface extends Application_User_Interface
{
	public const RIGHT_CREATE_MEDIA = 'CreateMedia';
	public const RIGHT_EDIT_MEDIA = 'EditMedia';
	public const RIGHT_DELETE_MEDIA = 'DeleteMedia';
	public const RIGHT_VIEW_MEDIA = 'ViewMedia';
	public const RIGHT_ADMIN_MEDIA = 'AdminMedia';
}


```
###  Path: `/src/classes/Application/Media/MediaRightsTrait.php`

```php
namespace Application\Media;

use Application_User_Rights_Group as Application_User_Rights_Group;

trait MediaRightsTrait
{
	public function canViewMedia(): bool
	{
		/* ... */
	}


	public function canCreateMedia(): bool
	{
		/* ... */
	}


	public function canEditMedia(): bool
	{
		/* ... */
	}


	public function canDeleteMedia(): bool
	{
		/* ... */
	}


	public function canAdministrateMedia(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/MediaTagConnector.php`

```php
namespace Application\Media;

use Application\AppFactory as AppFactory;
use Application\Tags\TagRecord as TagRecord;
use Application\Tags\Taggables\TagConnector as TagConnector;

class MediaTagConnector extends TagConnector
{
	public function getByTag(TagRecord $tag): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Processor.php`

```php
namespace ;

use Application\Application as Application;

/**
 * The Media Processor is a helper class for the clientside
 * product media preparation scripts. It adds all the required
 * clientside scripts and statements for the products that
 * are added.
 *
 * @package Application
 * @subpackage Media
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Media_Processor
{
	public const ERROR_MISSING_MEDIA_CONFIGURATION = 660001;

	public function renderContent(): string
	{
		/* ... */
	}


	public function addContainer(Application_Media_Container $container)
	{
		/* ... */
	}


	public function addDocument(Application_Media_Document $document)
	{
		/* ... */
	}


	public function getDocumentsCount(): int
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/ThumbnailRenderer.php`

```php
namespace Application\Media;

use AppUtils\HTMLTag as HTMLTag;
use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\Traits\RenderableTrait as RenderableTrait;
use Application_Media_DocumentInterface as Application_Media_DocumentInterface;

class ThumbnailRenderer implements RenderableInterface
{
	use RenderableTrait;

	/**
	 * @param int|null $size
	 * @return $this
	 */
	public function setPreferredSize(?int $size): self
	{
		/* ... */
	}


	/**
	 * Renders a thumbnail image HTML tag for the document.
	 *
	 * @return string
	 */
	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaDocumentWriter.php`

```php
namespace Application\Media\Writer;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application_User as Application_User;
use DBHelper as DBHelper;
use Throwable as Throwable;

/**
 * The single namespaced write path for media documents.
 *
 * Every mutator requires an active DB transaction. File writes and record
 * deletion are not transactional, so this class provides its own failure
 * cleanup: a `create()` that fails after writing the target file removes it,
 * and a `replaceFile()` that fails after overwriting the stored file
 * restores the previous bytes from a backup copy. The residual window after
 * `replaceFile()` has returned (e.g. a failing transaction commit) is
 * accepted and documented in `Media/API/README.md`.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaDocumentWriter
{
	/**
	 * Creates a new media document from the given content.
	 *
	 * @param MediaFileContent $content
	 * @param string $name
	 * @param Application_User $author
	 * @param string $description
	 * @param string $keywords
	 * @return MediaRecord
	 * @throws MediaWriterException
	 */
	public function create(
		MediaFileContent $content,
		string $name,
		Application_User $author,
		string $description = '',
		string $keywords = '',
	): MediaRecord
	{
		/* ... */
	}


	/**
	 * Replaces the file content of an existing media document. The
	 * extension must match the record's current extension.
	 *
	 * Order of operations: the new size is stored on the record first (a DB
	 * failure here never touches the file), then the current file is backed
	 * up, then overwritten; a failure after the overwrite restores the
	 * backup before rethrowing, and the backup is always removed afterward.
	 *
	 * @param MediaRecord $record
	 * @param MediaFileContent $content
	 * @return void
	 * @throws MediaWriterException
	 */
	public function replaceFile(MediaRecord $record, MediaFileContent $content): void
	{
		/* ... */
	}


	/**
	 * Changes a media document's metadata. A `null` argument leaves the
	 * corresponding field unchanged.
	 *
	 * @param MediaRecord $record
	 * @param string|NULL $name
	 * @param string|NULL $description
	 * @param string|NULL $keywords
	 * @return bool Whether anything was actually changed.
	 */
	public function updateMetadata(MediaRecord $record, ?string $name, ?string $description, ?string $keywords): bool
	{
		/* ... */
	}


	public function delete(MediaRecord $record): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaDocumentWriter.php`

```php
namespace Application\Media\Writer;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\AppFactory as AppFactory;
use Application\Application as Application;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application_User as Application_User;
use DBHelper as DBHelper;
use Throwable as Throwable;

/**
 * The single namespaced write path for media documents.
 *
 * Every mutator requires an active DB transaction. File writes and record
 * deletion are not transactional, so this class provides its own failure
 * cleanup: a `create()` that fails after writing the target file removes it,
 * and a `replaceFile()` that fails after overwriting the stored file
 * restores the previous bytes from a backup copy. The residual window after
 * `replaceFile()` has returned (e.g. a failing transaction commit) is
 * accepted and documented in `Media/API/README.md`.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaDocumentWriter
{
	/**
	 * Creates a new media document from the given content.
	 *
	 * @param MediaFileContent $content
	 * @param string $name
	 * @param Application_User $author
	 * @param string $description
	 * @param string $keywords
	 * @return MediaRecord
	 * @throws MediaWriterException
	 */
	public function create(
		MediaFileContent $content,
		string $name,
		Application_User $author,
		string $description = '',
		string $keywords = '',
	): MediaRecord
	{
		/* ... */
	}


	/**
	 * Replaces the file content of an existing media document. The
	 * extension must match the record's current extension.
	 *
	 * Order of operations: the new size is stored on the record first (a DB
	 * failure here never touches the file), then the current file is backed
	 * up, then overwritten; a failure after the overwrite restores the
	 * backup before rethrowing, and the backup is always removed afterward.
	 *
	 * @param MediaRecord $record
	 * @param MediaFileContent $content
	 * @return void
	 * @throws MediaWriterException
	 */
	public function replaceFile(MediaRecord $record, MediaFileContent $content): void
	{
		/* ... */
	}


	/**
	 * Changes a media document's metadata. A `null` argument leaves the
	 * corresponding field unchanged.
	 *
	 * @param MediaRecord $record
	 * @param string|NULL $name
	 * @param string|NULL $description
	 * @param string|NULL $keywords
	 * @return bool Whether anything was actually changed.
	 */
	public function updateMetadata(MediaRecord $record, ?string $name, ?string $description, ?string $keywords): bool
	{
		/* ... */
	}


	public function delete(MediaRecord $record): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaFileContent.php`

```php
namespace Application\Media\Writer;

use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\Application as Application;

/**
 * Immutable value object carrying file content and its file name, used
 * throughout the media writer services ({@see MediaFileValidator},
 * {@see MediaDocumentWriter}).
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileContent
{
	/**
	 * Creates an instance from raw binary content and a file name.
	 *
	 * @param string $fileName
	 * @param string $content
	 * @return self
	 * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
	 */
	public static function fromBinary(string $fileName, string $content): self
	{
		/* ... */
	}


	/**
	 * Creates an instance by reading an existing file from disk.
	 *
	 * @param FileInfo $file
	 * @return self
	 * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
	 */
	public static function fromFile(FileInfo $file): self
	{
		/* ... */
	}


	public function getFileName(): string
	{
		/* ... */
	}


	/**
	 * The file name without its extension.
	 * @return string
	 */
	public function getBaseName(): string
	{
		/* ... */
	}


	/**
	 * The file extension, lowercased, without the leading dot.
	 * @return string
	 */
	public function getExtension(): string
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	public function getSize(): int
	{
		/* ... */
	}


	/**
	 * Writes the content to a new temporary file and returns it. Used only
	 * by {@see MediaDocumentWriter} for the `setSourceFile()` copy in a real
	 * write; validation never calls this.
	 *
	 * @return FileInfo
	 */
	public function writeTempFile(): FileInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaFileContent.php`

```php
namespace Application\Media\Writer;

use AppUtils\FileHelper\FileInfo as FileInfo;
use Application\Application as Application;

/**
 * Immutable value object carrying file content and its file name, used
 * throughout the media writer services ({@see MediaFileValidator},
 * {@see MediaDocumentWriter}).
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileContent
{
	/**
	 * Creates an instance from raw binary content and a file name.
	 *
	 * @param string $fileName
	 * @param string $content
	 * @return self
	 * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
	 */
	public static function fromBinary(string $fileName, string $content): self
	{
		/* ... */
	}


	/**
	 * Creates an instance by reading an existing file from disk.
	 *
	 * @param FileInfo $file
	 * @return self
	 * @throws MediaWriterException {@see MediaWriterException::VALIDATION_INVALID_FILE_NAME}
	 */
	public static function fromFile(FileInfo $file): self
	{
		/* ... */
	}


	public function getFileName(): string
	{
		/* ... */
	}


	/**
	 * The file name without its extension.
	 * @return string
	 */
	public function getBaseName(): string
	{
		/* ... */
	}


	/**
	 * The file extension, lowercased, without the leading dot.
	 * @return string
	 */
	public function getExtension(): string
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	public function getSize(): int
	{
		/* ... */
	}


	/**
	 * Writes the content to a new temporary file and returns it. Used only
	 * by {@see MediaDocumentWriter} for the `setSourceFile()` copy in a real
	 * write; validation never calls this.
	 *
	 * @return FileInfo
	 */
	public function writeTempFile(): FileInfo
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaFileValidator.php`

```php
namespace Application\Media\Writer;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppFactory as AppFactory;
use Application_Media_Document as Application_Media_Document;

/**
 * Stateless, in-memory validator for media file content. It is the single
 * validation path for media writes: {@see MediaDocumentWriter::create()} and
 * {@see MediaDocumentWriter::replaceFile()} call it, and the dry-run
 * branches of the Add and Update media API methods call the same
 * {@see self::validate()} directly, so a dry run cannot drift from the real
 * write.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileValidator
{
	/**
	 * Validates the given file content, in order:
	 *
	 * 1. the extension resolves to a known media type
	 * 2. the type matches `$requiredTypeID`, when one is given
	 * 3. the content is non-empty
	 * 4. the content is valid per the resolved type's `isContentValid()`,
	 *    entirely in memory
	 *
	 * No temp file is ever created by this method.
	 *
	 * @param MediaFileContent $content
	 * @param string|NULL $requiredTypeID When given, the content's resolved
	 *        type must match this type ID exactly.
	 * @return string The resolved media type ID (e.g. `Image`, `PDF`).
	 * @throws MediaWriterException A `VALIDATION_*` code with a client-safe message.
	 */
	public function validate(MediaFileContent $content, ?string $requiredTypeID = null): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaFileValidator.php`

```php
namespace Application\Media\Writer;

use AppUtils\ClassHelper as ClassHelper;
use Application\AppFactory as AppFactory;
use Application_Media_Document as Application_Media_Document;

/**
 * Stateless, in-memory validator for media file content. It is the single
 * validation path for media writes: {@see MediaDocumentWriter::create()} and
 * {@see MediaDocumentWriter::replaceFile()} call it, and the dry-run
 * branches of the Add and Update media API methods call the same
 * {@see self::validate()} directly, so a dry run cannot drift from the real
 * write.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaFileValidator
{
	/**
	 * Validates the given file content, in order:
	 *
	 * 1. the extension resolves to a known media type
	 * 2. the type matches `$requiredTypeID`, when one is given
	 * 3. the content is non-empty
	 * 4. the content is valid per the resolved type's `isContentValid()`,
	 *    entirely in memory
	 *
	 * No temp file is ever created by this method.
	 *
	 * @param MediaFileContent $content
	 * @param string|NULL $requiredTypeID When given, the content's resolved
	 *        type must match this type ID exactly.
	 * @return string The resolved media type ID (e.g. `Image`, `PDF`).
	 * @throws MediaWriterException A `VALIDATION_*` code with a client-safe message.
	 */
	public function validate(MediaFileContent $content, ?string $requiredTypeID = null): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaWriterException.php`

```php
namespace Application\Media\Writer;

use Application\Media\MediaException as MediaException;

/**
 * Exception thrown by the media writer services ({@see MediaFileContent},
 * {@see MediaFileValidator}, {@see MediaDocumentWriter}).
 *
 * Its `VALIDATION_*` constants are the single source of truth for the
 * writer's client-facing validation codes; no other class re-declares them.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaWriterException extends MediaException
{
	public const VALIDATION_UNSUPPORTED_EXTENSION = 681001;
	public const VALIDATION_TYPE_MISMATCH = 681002;
	public const VALIDATION_CONTENT_MISMATCH = 681003;
	public const VALIDATION_EMPTY_FILE = 681004;
	public const VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED = 681005;
	public const VALIDATION_INVALID_FILE_NAME = 681006;
	public const ERROR_NO_TRANSACTION = 681101;
	public const ERROR_FILE_WRITE_FAILED = 681102;

	/**
	 * Whether this exception's code is one of the client-facing validation
	 * codes (`VALIDATION_*`, the `6810xx` block), as opposed to an internal
	 * error code (`ERROR_*`, the `6811xx` block).
	 *
	 * @return bool
	 */
	public function isValidationError(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/Writer/MediaWriterException.php`

```php
namespace Application\Media\Writer;

use Application\Media\MediaException as MediaException;

/**
 * Exception thrown by the media writer services ({@see MediaFileContent},
 * {@see MediaFileValidator}, {@see MediaDocumentWriter}).
 *
 * Its `VALIDATION_*` constants are the single source of truth for the
 * writer's client-facing validation codes; no other class re-declares them.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaWriterException extends MediaException
{
	public const VALIDATION_UNSUPPORTED_EXTENSION = 681001;
	public const VALIDATION_TYPE_MISMATCH = 681002;
	public const VALIDATION_CONTENT_MISMATCH = 681003;
	public const VALIDATION_EMPTY_FILE = 681004;
	public const VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED = 681005;
	public const VALIDATION_INVALID_FILE_NAME = 681006;
	public const ERROR_NO_TRANSACTION = 681101;
	public const ERROR_FILE_WRITE_FAILED = 681102;

	/**
	 * Whether this exception's code is one of the client-facing validation
	 * codes (`VALIDATION_*`, the `6810xx` block), as opposed to an internal
	 * error code (`ERROR_*`, the `6811xx` block).
	 *
	 * @return bool
	 */
	public function isValidationError(): bool
	{
		/* ... */
	}
}


```