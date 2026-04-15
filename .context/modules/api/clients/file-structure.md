# API Clients - File Structure
_SOURCE: API Clients and Admin File Structure_
# API Clients and Admin File Structure
###  
```
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Admin/
                    ├── APIClientRecordURLs.php
                    ├── APICollectionURLs.php
                    ├── APIKeyCollectionURLs.php
                    ├── APIKeyURLs.php
                    ├── APIScreenRights.php
                    ├── RequestTypes/
                    │   ├── APIClientRequestInterface.php
                    │   ├── APIClientRequestTrait.php
                    │   ├── APIClientRequestType.php
                    ├── Screens/
                    │   ├── APIClientsArea.php
                    │   ├── Mode/
                    │   │   └── ClientsListMode.php
                    │   │   └── CreateClientMode.php
                    │   │   └── View/
                    │   │       ├── APIKeys/
                    │   │       │   ├── APIKeySettingsAction.php
                    │   │       │   ├── APIKeyStatusAction.php
                    │   │       │   ├── APIKeysListAction.php
                    │   │       │   ├── CreateAPIKeyAction.php
                    │   │       ├── APIKeysSubmode.php
                    │   │       ├── ClientSettingsSubmode.php
                    │   │       ├── ClientStatusSubmode.php
                    │   │   └── ViewClientMode.php
                    ├── Traits/
                    │   └── APIKeyActionInterface.php
                    │   └── APIKeyActionRecordInterface.php
                    │   └── APIKeyActionRecordTrait.php
                    │   └── APIKeyActionTrait.php
                    │   └── ClientModeInterface.php
                    │   └── ClientModeTrait.php
                    │   └── ClientSubmodeInterface.php
                    │   └── ClientSubmodeTrait.php
                └── Clients/
                    └── API/
                        ├── APIKeyMethodInterface.php
                        ├── APIKeyMethodTrait.php
                        ├── Params/
                        │   └── APIKeyHandler.php
                        │   └── APIKeyParam.php
                    └── APIClientException.php
                    └── APIClientFilterCriteria.php
                    └── APIClientFilterSettings.php
                    └── APIClientRecord.php
                    └── APIClientRecordSettings.php
                    └── APIClientsCollection.php
                    └── Keys/
                        └── APIKeyException.php
                        └── APIKeyFilterCriteria.php
                        └── APIKeyFilterSettings.php
                        └── APIKeyMethods.php
                        └── APIKeyRecord.php
                        └── APIKeyRecordSettings.php
                        └── APIKeysCollection.php

```
---
**File Statistics**
- **Size**: 3.34 KB
- **Lines**: 72
File: `modules/api/clients/file-structure.md`
