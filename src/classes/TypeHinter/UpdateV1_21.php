<?php

declare(strict_types=1);

class TypeHinter_UpdateV1_21
{
    private static string $defActionSearch = <<<EOT
function getDefaultAction() : string
    {
        return null;
    }
EOT;

    private static string $defActionReplace = <<<EOT
function getDefaultAction() : string
    {
        return '';
    }
EOT;

    private static string $defSubmodeSearch = <<<EOT
function getDefaultSubmode() : string
    {
        return '';
    }
EOT;

    private static string $defSubmodeReplace = <<<EOT
function getDefaultSubmode() : string
    {
        return '';
    }
EOT;

    public function getActionSearch() : string
    {
        return self::$defActionSearch;
    }

    public function getActionReplace() : string
    {
        return self::$defActionReplace;
    }

    public function create(string $path) : TypeHinter
    {
        return (new TypeHinter($path))
            ->addMethod('_handleBeforeActions', 'void')
            ->addMethod('_handleActions', 'bool')
            ->addMethod('_handleSubactions', 'void')
            ->addMethod('_handleSubnavigation', 'void')
            ->addMethod('_handleBreadcrumb', 'void')
            ->addMethod('_handleSidebar', 'void')
            ->addMethod('_handleContextMenu', 'void')
            ->addMethod('_handleBreadcrumb', 'void')
            ->addMethod('_handleHelp', 'void')
            ->addMethod('_handleTabs', 'void')
            ->addMethod('_initSteps', 'void')
            ->addMethod('_filterFormValues', 'array')

            ->addMethod('createCollection', 'DBHelper_BaseCollection', '_Area_')
            ->addMethod('getTitle', 'string', '_Area_')
            ->addMethod('init', 'void', '_Area_')
            ->addMethod('_init', 'void', '_Area_')
            ->addMethod('initDone', 'void', '_Area_')
            ->addMethod('getDefaultData', 'array', '_Area_')
            ->addMethod('getLabel', 'string', '_Area_')
            ->addMethod('preProcess', 'void', '_Area_')
            ->addMethod('_process', 'bool', '_Area_')
            ->addMethod('render', 'string', '_Area_')
            ->addMethod('getMonitoredSteps', 'array', '_Area_')
            ->addMethod('getCancelURL', 'string', '_Area_')
            ->addMethod('_handle_stepUpdated', 'void', '_Area_')
            ->addMethod('configureColumns', 'void', '_Area_')
            ->addMethod('configureActions', 'void', '_Area_')
            ->addMethod('_reset', 'void', '_Area_')

            ->addMethod('createCollection', 'DBHelper_BaseCollection', '_Admin_')
            ->addMethod('getTitle', 'string', '_Admin_')
            ->addMethod('getLabel', 'string', '_Admin_')
            ->addMethod('init', 'void', '_Admin_')
            ->addMethod('_init', 'void', '_Admin_')
            ->addMethod('initDone', 'void', '_Admin_')
            ->addMethod('getDefaultData', 'array', '_Admin_')
            ->addMethod('getLabel', 'string', '_Admin_')
            ->addMethod('preProcess', 'void', '_Admin_')
            ->addMethod('_process', 'bool', '_Admin_')
            ->addMethod('render', 'string', '_Admin_')
            ->addMethod('getMonitoredSteps', 'array', '_Admin_')
            ->addMethod('getCancelURL', 'string', '_Admin_')
            ->addMethod('_handle_stepUpdated', 'void', '_Admin_')
            ->addMethod('configureColumns', 'void', '_Admin_')
            ->addMethod('configureActions', 'void', '_Admin_')
            ->addMethod('_reset', 'void', '_Admin_')

            ->addMethod('getRecordLabel', 'string')
            ->addMethod('getCollectionLabel', 'string')
            ->addMethod('getRecordTypeName', 'string')
            ->addMethod('getRecordPrimaryName', 'string')
            ->addMethod('getRecordTableName', 'string')
            ->addMethod('getRecordSearchableColumns', 'array')
            ->addMethod('getRecordDefaultSortKey', 'string')
            ->addMethod('getRecordFilterSettingsClassName', 'string')
            ->addMethod('getRecordFiltersClassName', 'string')
            ->addMethod('getRecordClassName', 'string')
            ->addMethod('getRecordProperties', 'array')
            ->addMethod('getRecordDefaultSortDir', 'string')
            ->addMethod('getParentCollectionClass', 'string')

            ->addMethod('_configureFilters', 'void')
            ->addMethod('prepareQuery', 'void')
            ->addMethod('registerSettings', 'void')
            ->addMethod('getURLName', 'string')
            ->addMethod('getBackOrCancelURL', 'string')
            ->addMethod('getRecordMissingURL', 'string')
            ->addMethod('getDefaultMode', 'string')
            ->addMethod('getDefaultSubmode', 'string')
            ->addMethod('getDefaultAction', 'string')
            ->addMethod('getNavigationTitle', 'string')
            ->addMethod('getNavigationGroup', 'string')
            ->addMethod('isCore', 'bool')
            ->addMethod('getDependencies', 'array')
            ->addMethod('isUserAllowed', 'bool')
            ->addMethod('injectElements', 'void')
            ->addReplace(self::$defActionSearch, self::$defActionReplace)
            ->addReplace(self::$defSubmodeSearch, self::$defSubmodeReplace);
    }
}
