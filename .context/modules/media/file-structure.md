# Media - File Structure
_SOURCE: Media Module File Tree_
# Media Module File Tree
###  
```
└── src/
    └── classes/
        └── Application/
            └── Media/
                └── API/
                    ├── MediaAPIGroup.php
                    ├── MediaAPIResponseInterface.php
                    ├── MediaDocumentAPIInterface.php
                    ├── MediaDocumentAPITrait.php
                    ├── MediaDocumentParamsContainer.php
                    ├── MediaDocumentSerializer.php
                    ├── MediaFileUploadAPIInterface.php
                    ├── MediaFileUploadAPITrait.php
                    ├── MediaMetadataAPIInterface.php
                    ├── MediaMetadataAPITrait.php
                    ├── Methods/
                    │   ├── AddMediaDocumentAPI.php
                    │   ├── DeleteMediaDocumentAPI.php
                    │   ├── GetMediaDocumentAPI.php
                    │   ├── GetMediaDocumentsAPI.php
                    │   ├── UpdateMediaDocumentAPI.php
                    ├── Params/
                    │   └── MediaIDHandler.php
                    │   └── MediaIDParam.php
                └── Admin/
                    ├── MediaAdminURLs.php
                    ├── MediaRecordAdminURLs.php
                    ├── MediaScreenRights.php
                    ├── Screens/
                    │   ├── MediaLibraryArea.php
                    │   ├── Mode/
                    │   │   └── CreateMode.php
                    │   │   └── GlobalSettingsMode.php
                    │   │   └── ImageGalleryMode.php
                    │   │   └── ListMode.php
                    │   │   └── View/
                    │   │       ├── SettingsSubmode.php
                    │   │       ├── StatusSubmode.php
                    │   │       ├── TagsSubmode.php
                    │   │   └── ViewMode.php
                    ├── Traits/
                    │   └── MediaModeInterface.php
                    │   └── MediaModeTrait.php
                    │   └── MediaViewInterface.php
                    │   └── MediaViewTrait.php
                └── Collection/
                    ├── MediaCollection.php
                    ├── MediaFilterCriteria.php
                    ├── MediaFilterSettings.php
                    ├── MediaRecord.php
                    ├── MediaSettingsManager.php
                └── Configuration.php
                └── Configuration/
                    ├── Image.php
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
                └── Events/
                    ├── RegisterMediaTagsListener.php
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