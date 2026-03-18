# DBHelper - File Structure
_SOURCE: PHP class file tree_
# PHP class file tree
###  
```
└── src/
    └── classes/
        └── DBHelper/
            └── API/
                ├── Methods/
                │   └── DescribeCollectionsAPI.php
            └── Admin/
                ├── BaseCollectionListBuilder.php
                ├── BaseDBRecordSelectionTieIn.php
                ├── DBHelperAdminException.php
                ├── DBRecordSelectionTieInInterface.php
                ├── Requests/
                │   ├── BaseDBRecordRequestType.php
                ├── Screens/
                │   ├── Action/
                │   │   ├── BaseRecordAction.php
                │   │   ├── BaseRecordCreateAction.php
                │   │   ├── BaseRecordDeleteAction.php
                │   │   ├── BaseRecordListAction.php
                │   │   ├── BaseRecordSettingsAction.php
                │   │   ├── BaseRecordStatusAction.php
                │   ├── Mode/
                │   │   ├── BaseRecordCreateMode.php
                │   │   ├── BaseRecordListMode.php
                │   │   ├── BaseRecordMode.php
                │   ├── Submode/
                │   │   └── BaseRecordCreateSubmode.php
                │   │   └── BaseRecordDeleteSubmode.php
                │   │   └── BaseRecordListSubmode.php
                │   │   └── BaseRecordSettingsSubmode.php
                │   │   └── BaseRecordStatusSubmode.php
                │   │   └── BaseRecordSubmode.php
                ├── Traits/
                │   └── RecordCreateScreenInterface.php
                │   └── RecordCreateScreenTrait.php
                │   └── RecordDeleteScreenInterface.php
                │   └── RecordDeleteScreenTrait.php
                │   └── RecordEditScreenInterface.php
                │   └── RecordEditScreenTrait.php
                │   └── RecordListScreenInterface.php
                │   └── RecordListScreenTrait.php
                │   └── RecordScreenInterface.php
                │   └── RecordScreenTrait.php
                │   └── RecordSettingsScreenInterface.php
                │   └── RecordSettingsScreenTrait.php
                │   └── RecordStatusScreenInterface.php
                │   └── RecordStatusScreenTrait.php
            └── Attributes/
                ├── UncachedQuery.php
            └── BaseCollection.php
            └── BaseCollection/
                ├── BaseChildCollection.php
                ├── ChildCollectionInterface.php
                ├── DBHelperCollectionException.php
                ├── DBHelperCollectionInterface.php
                ├── Event/
                │   ├── AfterCreateRecordEvent.php
                │   ├── AfterDeleteRecordEvent.php
                │   ├── BeforeCreateRecordEvent.php
                ├── Keys.php
                ├── Keys/
                │   ├── Key.php
                ├── OperationContext.php
                ├── OperationContext/
                │   └── Create.php
                │   └── Delete.php
                │   └── Save.php
            └── BaseFilterCriteria.php
            └── BaseFilterCriteria/
                ├── BaseCollectionFilteringInterface.php
                ├── IntegerCollectionFilteringInterface.php
                ├── Record.php
                ├── StringCollectionFilteringInterface.php
            └── BaseFilterSettings.php
            └── BaseRecord.php
            └── BaseRecord/
                ├── BaseRecordDecorator.php
                ├── BaseRecordException.php
                ├── Event/
                │   └── KeyModifiedEvent.php
            └── BaseRecordSettings.php
            └── CaseStatement.php
            └── DBHelper.php
            └── DBHelperFilterCriteriaInterface.php
            └── DBHelperFilterSettingsInterface.php
            └── DataTable.php
            └── DataTable/
                ├── Events/
                │   └── KeysDeleted.php
                │   └── KeysSaved.php
            └── Docs/
                ├── dbhelper-database-abstraction.md
                ├── dbhelper-record-collections.md
            └── Event.php
            └── Exception.php
            └── Exception/
                ├── BaseErrorRenderer.php
                ├── CLIErrorRenderer.php
                ├── HTMLErrorRenderer.php
            └── FetchBase.php
            └── FetchKey.php
            └── FetchMany.php
            └── FetchOne.php
            └── Interfaces/
                ├── DBHelperRecordInterface.php
            └── OperationTypes.php
            └── README.md
            └── StatementBuilder.php
            └── StatementBuilder/
                ├── ValueDefinition.php
                ├── ValuesContainer.php
            └── TrackedQuery.php
            └── Traits/
                ├── AfterRecordCreatedEventTrait.php
                ├── BeforeCreateEventTrait.php
                ├── LooseDBRecordInterface.php
                ├── LooseDBRecordTrait.php
                ├── RecordDecoratorInterface.php
                ├── RecordDecoratorTrait.php
                ├── RecordKeyHandlersTrait.php
            └── module-context.yaml

```
---
**File Statistics**
- **Size**: 7.49 KB
- **Lines**: 134
File: `modules/db-helper/file-structure.md`
