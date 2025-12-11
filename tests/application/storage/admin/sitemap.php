<?php

declare(strict_types=1);

return array (
  'urlPaths' => 
  array (
    'api-clients' => 'TestDriver\\Area\\APIClientsArea',
    'api-clients.create' => 'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode',
    'api-clients.list' => 'TestDriver\\Area\\APIClientsArea\\ClientsListMode',
    'api-clients.view' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode',
    'api-clients.view.api_keys' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode',
    'api-clients.view.api_keys.create' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction',
    'api-clients.view.api_keys.list' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction',
    'api-clients.view.api_keys.settings' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction',
    'api-clients.view.api_keys.status' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction',
    'api-clients.view.settings' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode',
    'api-clients.view.status' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode',
    'countries' => 'TestDriver\\Area\\CountriesScreen',
    'countries.create' => 'TestDriver\\Area\\CountriesScreen\\CreateScreen',
    'countries.list' => 'TestDriver\\Area\\CountriesScreen\\ListScreen',
    'countries.view' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen',
    'countries.view.settings' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen',
    'countries.view.status' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen',
    'day' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\DayListScreen',
    'devel' => 'Application\\Development\\Admin\\Screens\\DevelArea',
    'devel.appconfig' => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
    'devel.appinterface' => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
    'devel.appsets' => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
    'devel.appsets.create' => 'Application\\Sets\\Admin\\Screens\\CreateSetSubmode',
    'devel.appsets.delete' => 'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode',
    'devel.appsets.edit' => 'Application\\Sets\\Admin\\Screens\\EditSetSubmode',
    'devel.appsets.list' => 'Application\\Sets\\Admin\\Screens\\SetsListSubmode',
    'devel.appsettings' => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
    'devel.cache-control' => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
    'devel.css-gen' => 'UI\\Admin\\Screens\\CSSGenDevelMode',
    'devel.dbdump' => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
    'devel.deploy-history' => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
    'devel.errorlog' => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
    'devel.errorlog.list' => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
    'devel.errorlog.view' => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
    'devel.maintenance' => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
    'devel.maintenance.create' => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
    'devel.messagelog' => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
    'devel.overview' => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
    'devel.renamer' => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
    'devel.renamer.configuration' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
    'devel.renamer.export' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
    'devel.renamer.replace' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
    'devel.renamer.results' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
    'devel.renamer.search' => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
    'devel.rightsoverview' => 'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode',
    'devel.whatsneweditor' => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
    'devel.whatsneweditor.edit' => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
    'global' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalListScreen',
    'image-gallery' => 'Application\\Admin\\Area\\Media\\BaseImageGalleryScreen',
    'media' => 'TestDriver\\Area\\MediaLibraryScreen',
    'media.create' => 'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen',
    'media.image-gallery' => 'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen',
    'media.list' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen',
    'media.settings' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen',
    'media.view' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen',
    'media.view.status' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen',
    'media.view.tagging' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen',
    'news' => 'TestDriver\\Area\\NewsScreen',
    'news.categories-list' => 'TestDriver\\Area\\NewsScreen\\CategoriesListScreen',
    'news.create-alert' => 'TestDriver\\Area\\NewsScreen\\CreateAlertScreen',
    'news.create-article' => 'TestDriver\\Area\\NewsScreen\\CreateArticleScreen',
    'news.create-category' => 'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen',
    'news.list' => 'TestDriver\\Area\\NewsScreen\\NewsListScreen',
    'news.read' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen',
    'news.read.article' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen',
    'news.read.articles' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen',
    'news.view' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen',
    'news.view-category' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen',
    'news.view-category.settings' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen',
    'news.view.settings' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen',
    'news.view.status' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen',
    'quicknav' => 'TestDriver\\Area\\QuickNavScreen',
    'revisionable' => 'TestDriver\\Area\\RevisionableScreen',
    'revisionable.list' => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
    'settings' => 'Application_Admin_Area_Settings',
    'tag-tree' => 'Application\\Area\\Tags\\ViewTag\\BaseTagTreeScreen',
    'tagging' => 'Application\\Admin\\Area\\Media\\View\\BaseMediaTagsScreen',
    'tags' => 'TestDriver\\Area\\TagsScreen',
    'tags.create' => 'TestDriver\\Area\\TagsScreen\\CreateTagScreen',
    'tags.list' => 'TestDriver\\Area\\TagsScreen\\TagListScreen',
    'tags.view-tag' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen',
    'tags.view-tag.tag-settings' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen',
    'tags.view-tag.tag-tree' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen',
    'testing' => 'TestDriver\\Area\\TestingScreen',
    'testing.cancel-handle-actions' => 'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen',
    'testing.collection-create-basic' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen',
    'testing.collection-create-legacy' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen',
    'testing.collection-create-manager-ex' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen',
    'testing.dbhelper-selection-tiein' => 'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen',
    'testing.log-javascript-error' => 'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen',
    'testing.overview' => 'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen',
    'testing.replace-content' => 'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen',
    'testing.tiein-ancestry-test' => 'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen',
    'time-settings' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalSettingsScreen',
    'time-spans-list' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\TimeSpansListScreen',
    'time-tracker' => 'TestDriver\\Area\\TimeTrackerScreen',
    'time-tracker.auto-fill' => 'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen',
    'time-tracker.create-time-span' => 'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen',
    'time-tracker.export' => 'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen',
    'time-tracker.import' => 'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen',
    'translations' => 'TestDriver\\Area\\TranslationsScreen',
    'users' => 'TestDriver\\Area\\UsersArea',
    'users.create' => 'TestDriver\\Area\\UsersArea\\CreateUserMode',
    'users.list' => 'TestDriver\\Area\\UsersArea\\UserListMode',
    'users.view' => 'TestDriver\\Area\\UsersArea\\ViewUserMode',
    'users.view.settings' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode',
    'users.view.status' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode',
    'welcome' => 'TestDriver\\Area\\WelcomeScreen',
    'welcome.overview' => 'TestDriver\\Area\\WelcomeScreen\\OverviewScreen',
    'wizardtest' => 'TestDriver_Area_WizardTest',
    'wizardtest.wizard' => 'TestDriver_Area_WizardTest_Wizard',
  ),
  'flat' => 
  array (
    'Application\\Admin\\Area\\BaseMediaLibraryScreen' => 
    array (
      'id' => 'BaseMediaLibraryScreen',
      'urlName' => 'media',
      'urlPath' => 'media',
      'title' => 'Media library',
      'navigationTitle' => 'Media',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application\\Admin\\Area\\BaseMediaLibraryScreen',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Area\\Media\\BaseImageGalleryScreen' => 
    array (
      'id' => 'BaseImageGalleryScreen',
      'urlName' => 'image-gallery',
      'urlPath' => 'image-gallery',
      'title' => 'Image gallery',
      'navigationTitle' => 'Image gallery',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application\\Admin\\Area\\Media\\BaseImageGalleryScreen',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area/Media',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Area\\Media\\View\\BaseMediaTagsScreen' => 
    array (
      'id' => 'BaseMediaTagsScreen',
      'urlName' => 'tagging',
      'urlPath' => 'tagging',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => 'EditMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Area\\Media\\View\\BaseMediaTagsScreen',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area/Media/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode' => 
    array (
      'id' => 'AppSettingsDevelMode',
      'urlName' => 'appsettings',
      'urlPath' => 'devel.appsettings',
      'title' => 'Application settings',
      'navigationTitle' => 'Application settings',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/AppSettings/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Area\\Tags\\ViewTag\\BaseTagTreeScreen' => 
    array (
      'id' => 'BaseTagTreeScreen',
      'urlName' => 'tag-tree',
      'urlPath' => 'tag-tree',
      'title' => 'Tag tree',
      'navigationTitle' => 'Tree',
      'requiredRight' => 'EditTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Area\\Tags\\ViewTag\\BaseTagTreeScreen',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area/Tags/ViewTag',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\CacheControl\\Admin\\Screens\\CacheControlMode' => 
    array (
      'id' => 'CacheControlMode',
      'urlName' => 'cache-control',
      'urlPath' => 'devel.cache-control',
      'title' => 'Cache control',
      'navigationTitle' => 'Cache control',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/CacheControl/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode' => 
    array (
      'id' => 'DeploymentHistoryMode',
      'urlName' => 'deploy-history',
      'urlPath' => 'devel.deploy-history',
      'title' => 'Deployment history',
      'navigationTitle' => 'Deployment history',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/DeploymentRegistry/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode' => 
    array (
      'id' => 'DatabaseDumpDevMode',
      'urlName' => 'dbdump',
      'urlPath' => 'devel.dbdump',
      'title' => 'Database dumps',
      'navigationTitle' => 'Database dumps',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Development\\Admin\\Screens\\DevelArea' => 
    array (
      'id' => 'DevelArea',
      'urlName' => 'devel',
      'urlPath' => 'devel',
      'title' => 'Developer tools',
      'navigationTitle' => 'Developer tools',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Development\\Admin\\Screens\\DevelArea',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
        1 => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
        2 => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
        3 => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
        4 => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
        5 => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
        6 => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
        7 => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
        8 => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
        9 => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
        10 => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
        11 => 'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode',
        12 => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
        13 => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
        14 => 'UI\\Admin\\Screens\\CSSGenDevelMode',
      ),
    ),
    'Application\\Development\\Admin\\Screens\\DevelOverviewMode' => 
    array (
      'id' => 'DevelOverviewMode',
      'urlName' => 'overview',
      'urlPath' => 'devel.overview',
      'title' => 'Developer tools overview',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Environments\\Admin\\Screens\\AppConfigMode' => 
    array (
      'id' => 'AppConfigMode',
      'urlName' => 'appconfig',
      'urlPath' => 'devel.appconfig',
      'title' => 'Application configuration',
      'navigationTitle' => 'Configuration',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Environments/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode' => 
    array (
      'id' => 'ErrorLogMode',
      'urlName' => 'errorlog',
      'urlPath' => 'devel.errorlog',
      'title' => 'Error log',
      'navigationTitle' => 'Error log',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
        1 => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
      ),
    ),
    'Application\\ErrorLog\\Admin\\Screens\\ListSubmode' => 
    array (
      'id' => 'ListSubmode',
      'urlName' => 'list',
      'urlPath' => 'devel.errorlog.list',
      'title' => 'Error Log Entries',
      'navigationTitle' => 'List',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode' => 
    array (
      'id' => 'ViewSubmode',
      'urlName' => 'view',
      'urlPath' => 'devel.errorlog.view',
      'title' => 'View error log',
      'navigationTitle' => 'View',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Maintenance\\Admin\\Screens\\CreateSubmode' => 
    array (
      'id' => 'CreateSubmode',
      'urlName' => 'create',
      'urlPath' => 'devel.maintenance.create',
      'title' => 'Create maintenance plan',
      'navigationTitle' => 'Create plan',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Maintenance/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode' => 
    array (
      'id' => 'MaintenanceMode',
      'urlName' => 'maintenance',
      'urlPath' => 'devel.maintenance',
      'title' => 'Planned maintenance',
      'navigationTitle' => 'Maintenance',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Maintenance/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
      ),
    ),
    'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode' => 
    array (
      'id' => 'MessageLogDevelMode',
      'urlName' => 'messagelog',
      'urlPath' => 'devel.messagelog',
      'title' => 'Application messagelog',
      'navigationTitle' => 'Messagelog',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Messagelogs/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode' => 
    array (
      'id' => 'RenamerMode',
      'urlName' => 'renamer',
      'urlPath' => 'devel.renamer',
      'title' => 'Database Renamer',
      'navigationTitle' => 'DB Renamer',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
        1 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
        2 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
        3 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
        4 => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode' => 
    array (
      'id' => 'ConfigurationSubmode',
      'urlName' => 'configuration',
      'urlPath' => 'devel.renamer.configuration',
      'title' => 'Configuration',
      'navigationTitle' => 'Configuration',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode' => 
    array (
      'id' => 'ExportSubmode',
      'urlName' => 'export',
      'urlPath' => 'devel.renamer.export',
      'title' => 'Export',
      'navigationTitle' => 'Export',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode' => 
    array (
      'id' => 'ReplaceSubmode',
      'urlName' => 'replace',
      'urlPath' => 'devel.renamer.replace',
      'title' => 'Replace',
      'navigationTitle' => 'Replace',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode' => 
    array (
      'id' => 'ResultsSubmode',
      'urlName' => 'results',
      'urlPath' => 'devel.renamer.results',
      'title' => 'Results',
      'navigationTitle' => 'Results',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode' => 
    array (
      'id' => 'SearchSubmode',
      'urlName' => 'search',
      'urlPath' => 'devel.renamer.search',
      'title' => 'Search',
      'navigationTitle' => 'Search',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode' => 
    array (
      'id' => 'ApplicationSetsMode',
      'urlName' => 'appsets',
      'urlPath' => 'devel.appsets',
      'title' => 'Application interface sets',
      'navigationTitle' => 'Appsets',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Sets\\Admin\\Screens\\CreateSetSubmode',
        1 => 'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode',
        2 => 'Application\\Sets\\Admin\\Screens\\EditSetSubmode',
        3 => 'Application\\Sets\\Admin\\Screens\\SetsListSubmode',
      ),
    ),
    'Application\\Sets\\Admin\\Screens\\CreateSetSubmode' => 
    array (
      'id' => 'CreateSetSubmode',
      'urlName' => 'create',
      'urlPath' => 'devel.appsets.create',
      'title' => 'Create a new application set',
      'navigationTitle' => 'Create new set',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Sets\\Admin\\Screens\\CreateSetSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode' => 
    array (
      'id' => 'DeleteSetSubmode',
      'urlName' => 'delete',
      'urlPath' => 'devel.appsets.delete',
      'title' => 'Delete an application set',
      'navigationTitle' => 'Delete set',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Sets\\Admin\\Screens\\EditSetSubmode' => 
    array (
      'id' => 'EditSetSubmode',
      'urlName' => 'edit',
      'urlPath' => 'devel.appsets.edit',
      'title' => 'Create a new application set',
      'navigationTitle' => 'Create new set',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Sets\\Admin\\Screens\\EditSetSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Sets\\Admin\\Screens\\SetsListSubmode' => 
    array (
      'id' => 'SetsListSubmode',
      'urlName' => 'list',
      'urlPath' => 'devel.appsets.list',
      'title' => 'List of application sets',
      'navigationTitle' => 'List',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Sets\\Admin\\Screens\\SetsListSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode' => 
    array (
      'id' => 'RightsOverviewDevelMode',
      'urlName' => 'rightsoverview',
      'urlPath' => 'devel.rightsoverview',
      'title' => 'User rights overview',
      'navigationTitle' => 'User rights',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Users/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\WhatsNew\\Admin\\Screens\\EditSubmode' => 
    array (
      'id' => 'EditSubmode',
      'urlName' => 'edit',
      'urlPath' => 'devel.whatsneweditor.edit',
      'title' => 'Edit a version',
      'navigationTitle' => 'Edit',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/WhatsNew/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode' => 
    array (
      'id' => 'WhatsNewEditorMode',
      'urlName' => 'whatsneweditor',
      'urlPath' => 'devel.whatsneweditor',
      'title' => 'What\'s new editor',
      'navigationTitle' => 'What\'s new editor',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/WhatsNew/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
      ),
    ),
    'Application_Admin_Area_Settings' => 
    array (
      'id' => 'Settings',
      'urlName' => 'settings',
      'urlPath' => 'settings',
      'title' => 'User settings',
      'navigationTitle' => 'User settings',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application_Admin_Area_Settings',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application_Admin_Area_Welcome' => 
    array (
      'id' => 'Welcome',
      'urlName' => 'welcome',
      'urlPath' => 'welcome',
      'title' => 'Quickstart',
      'navigationTitle' => '',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application_Admin_Area_Welcome',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area',
      'subscreenClasses' => 
      array (
        0 => 'Application_Admin_Area_Welcome_Overview',
      ),
    ),
    'Application_Admin_Area_Welcome_Overview' => 
    array (
      'id' => 'Overview',
      'urlName' => 'overview',
      'urlPath' => 'welcome.overview',
      'title' => 'Quickstart',
      'navigationTitle' => 'Quickstart',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application_Admin_Area_Welcome_Overview',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area/Welcome',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea' => 
    array (
      'id' => 'APIClientsArea',
      'urlName' => 'api-clients',
      'urlPath' => 'api-clients',
      'title' => 'API Clients',
      'navigationTitle' => 'API Clients',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\APIClientsArea\\ClientsListMode',
        1 => 'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode',
        2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode',
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ClientsListMode' => 
    array (
      'id' => 'ClientsListMode',
      'urlName' => 'list',
      'urlPath' => 'api-clients.list',
      'title' => 'Overview',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
        'Multi-delete API Clients' => 'DeleteAPIClients',
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ClientsListMode',
      'path' => 'Area/APIClientsArea',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode' => 
    array (
      'id' => 'CreateAPIClientMode',
      'urlName' => 'create',
      'urlPath' => 'api-clients.create',
      'title' => 'Create a new API Client',
      'navigationTitle' => 'Create new client',
      'requiredRight' => 'CreateAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode',
      'path' => 'Area/APIClientsArea',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode' => 
    array (
      'id' => 'ViewAPIClientMode',
      'urlName' => 'view',
      'urlPath' => 'api-clients.view',
      'title' => 'View API Client',
      'navigationTitle' => 'View Client',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode',
      'path' => 'Area/APIClientsArea',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode',
        1 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode',
        2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode',
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode' => 
    array (
      'id' => 'APIClientSettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'api-clients.view.settings',
      'title' => 'API Client Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode' => 
    array (
      'id' => 'APIClientStatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'api-clients.view.status',
      'title' => 'API Client Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode' => 
    array (
      'id' => 'APIKeysSubmode',
      'urlName' => 'api_keys',
      'urlPath' => 'api-clients.view.api_keys',
      'title' => 'API keys',
      'navigationTitle' => 'API Keys',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction',
        1 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction',
        2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction',
        3 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction',
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction' => 
    array (
      'id' => 'APIKeySettingsAction',
      'urlName' => 'settings',
      'urlPath' => 'api-clients.view.api_keys.settings',
      'title' => 'API Key Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
        'Edit API Key Settings' => 'EditAPIClients',
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction' => 
    array (
      'id' => 'APIKeyStatusAction',
      'urlName' => 'status',
      'urlPath' => 'api-clients.view.api_keys.status',
      'title' => 'API Key Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction' => 
    array (
      'id' => 'APIKeysListAction',
      'urlName' => 'list',
      'urlPath' => 'api-clients.view.api_keys.list',
      'title' => 'Overview of API Keys',
      'navigationTitle' => 'Overview',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction' => 
    array (
      'id' => 'CreateAPIKeyAction',
      'urlName' => 'create',
      'urlPath' => 'api-clients.view.api_keys.create',
      'title' => 'Create an API Key',
      'navigationTitle' => 'Create new key',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction',
      'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\CountriesScreen' => 
    array (
      'id' => 'CountriesScreen',
      'urlName' => 'countries',
      'urlPath' => 'countries',
      'title' => 'Countries',
      'navigationTitle' => 'Countries',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\CountriesScreen\\CreateScreen',
        1 => 'TestDriver\\Area\\CountriesScreen\\ListScreen',
        2 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen',
      ),
    ),
    'TestDriver\\Area\\CountriesScreen\\CreateScreen' => 
    array (
      'id' => 'CreateScreen',
      'urlName' => 'create',
      'urlPath' => 'countries.create',
      'title' => 'Create a new country',
      'navigationTitle' => 'Create a country',
      'requiredRight' => 'CreateCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen\\CreateScreen',
      'path' => 'Area/CountriesScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\CountriesScreen\\ListScreen' => 
    array (
      'id' => 'ListScreen',
      'urlName' => 'list',
      'urlPath' => 'countries.list',
      'title' => 'Available countries',
      'navigationTitle' => 'List',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen\\ListScreen',
      'path' => 'Area/CountriesScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\CountriesScreen\\ViewScreen' => 
    array (
      'id' => 'ViewScreen',
      'urlName' => 'view',
      'urlPath' => 'countries.view',
      'title' => 'View a country',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen',
      'path' => 'Area/CountriesScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen',
        1 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen',
      ),
    ),
    'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen' => 
    array (
      'id' => 'SettingsScreen',
      'urlName' => 'settings',
      'urlPath' => 'countries.view.settings',
      'title' => 'Country settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'EditCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen',
      'path' => 'Area/CountriesScreen/ViewScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen' => 
    array (
      'id' => 'StatusScreen',
      'urlName' => 'status',
      'urlPath' => 'countries.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen',
      'path' => 'Area/CountriesScreen/ViewScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen' => 
    array (
      'id' => 'MediaLibraryScreen',
      'urlName' => 'media',
      'urlPath' => 'media',
      'title' => 'Media library',
      'navigationTitle' => 'Media',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\MediaLibraryScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen',
        1 => 'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen',
        2 => 'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen',
        3 => 'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen',
        4 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen',
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen' => 
    array (
      'id' => 'CreateMediaScreen',
      'urlName' => 'create',
      'urlPath' => 'media.create',
      'title' => 'Add a media file',
      'navigationTitle' => 'Add media',
      'requiredRight' => 'CreateMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen',
      'path' => 'Area/MediaLibraryScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen' => 
    array (
      'id' => 'ImageGalleryScreen',
      'urlName' => 'image-gallery',
      'urlPath' => 'media.image-gallery',
      'title' => 'Image gallery',
      'navigationTitle' => 'Image gallery',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen',
      'path' => 'Area/MediaLibraryScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen' => 
    array (
      'id' => 'MediaListScreen',
      'urlName' => 'list',
      'urlPath' => 'media.list',
      'title' => 'Available media files',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen',
      'path' => 'Area/MediaLibraryScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen' => 
    array (
      'id' => 'MediaSettingsScreen',
      'urlName' => 'settings',
      'urlPath' => 'media.settings',
      'title' => 'Media settings',
      'navigationTitle' => 'Media settings',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen',
      'path' => 'Area/MediaLibraryScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen' => 
    array (
      'id' => 'ViewMediaScreen',
      'urlName' => 'view',
      'urlPath' => 'media.view',
      'title' => 'Media file',
      'navigationTitle' => 'Media file',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen',
      'path' => 'Area/MediaLibraryScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen',
        1 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen',
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen' => 
    array (
      'id' => 'MediaStatusScreen',
      'urlName' => 'status',
      'urlPath' => 'media.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen',
      'path' => 'Area/MediaLibraryScreen/ViewMediaScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen' => 
    array (
      'id' => 'MediaTagsScreen',
      'urlName' => 'tagging',
      'urlPath' => 'media.view.tagging',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => 'EditMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen',
      'path' => 'Area/MediaLibraryScreen/ViewMediaScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen' => 
    array (
      'id' => 'NewsScreen',
      'urlName' => 'news',
      'urlPath' => 'news',
      'title' => 'Application news central',
      'navigationTitle' => 'News central',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\NewsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\NewsScreen\\CategoriesListScreen',
        1 => 'TestDriver\\Area\\NewsScreen\\CreateAlertScreen',
        2 => 'TestDriver\\Area\\NewsScreen\\CreateArticleScreen',
        3 => 'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen',
        4 => 'TestDriver\\Area\\NewsScreen\\NewsListScreen',
        5 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen',
        6 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen',
        7 => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen',
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\CategoriesListScreen' => 
    array (
      'id' => 'CategoriesListScreen',
      'urlName' => 'categories-list',
      'urlPath' => 'news.categories-list',
      'title' => 'Available categories',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\CategoriesListScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\CreateAlertScreen' => 
    array (
      'id' => 'CreateAlertScreen',
      'urlName' => 'create-alert',
      'urlPath' => 'news.create-alert',
      'title' => 'Create a news alert',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'CreateAlerts',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\CreateAlertScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\CreateArticleScreen' => 
    array (
      'id' => 'CreateArticleScreen',
      'urlName' => 'create-article',
      'urlPath' => 'news.create-article',
      'title' => 'Create a news article',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'CreateNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\CreateArticleScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen' => 
    array (
      'id' => 'CreateCategoryScreen',
      'urlName' => 'create-category',
      'urlPath' => 'news.create-category',
      'title' => 'Create a news category',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'EditNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\NewsListScreen' => 
    array (
      'id' => 'NewsListScreen',
      'urlName' => 'list',
      'urlPath' => 'news.list',
      'title' => 'Available news articles',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\NewsListScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ReadNewsScreen' => 
    array (
      'id' => 'ReadNewsScreen',
      'urlName' => 'read',
      'urlPath' => 'news.read',
      'title' => 'AppTestSuite news',
      'navigationTitle' => 'News',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen',
        1 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen',
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen' => 
    array (
      'id' => 'ReadArticleScreen',
      'urlName' => 'article',
      'urlPath' => 'news.read.article',
      'title' => 'News Article',
      'navigationTitle' => 'Article',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen',
      'path' => 'Area/NewsScreen/ReadNewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen' => 
    array (
      'id' => 'ReadArticlesScreen',
      'urlName' => 'articles',
      'urlPath' => 'news.read.articles',
      'title' => 'AppTestSuite news',
      'navigationTitle' => 'News',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen',
      'path' => 'Area/NewsScreen/ReadNewsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ViewArticleScreen' => 
    array (
      'id' => 'ViewArticleScreen',
      'urlName' => 'view',
      'urlPath' => 'news.view',
      'title' => 'View news entry',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen',
        1 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen',
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen' => 
    array (
      'id' => 'ArticleSettingsScreen',
      'urlName' => 'settings',
      'urlPath' => 'news.view.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
        'Modify the settings' => 'EditNews',
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen',
      'path' => 'Area/NewsScreen/ViewArticleScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen' => 
    array (
      'id' => 'ArticleStatusScreen',
      'urlName' => 'status',
      'urlPath' => 'news.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen',
      'path' => 'Area/NewsScreen/ViewArticleScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen' => 
    array (
      'id' => 'ViewCategoryScreen',
      'urlName' => 'view-category',
      'urlPath' => 'news.view-category',
      'title' => 'View news category',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen',
      'path' => 'Area/NewsScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen',
      ),
    ),
    'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen' => 
    array (
      'id' => 'CategorySettingsScreen',
      'urlName' => 'settings',
      'urlPath' => 'news.view-category.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
        'Modify the settings' => 'EditNews',
      ),
      'class' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen',
      'path' => 'Area/NewsScreen/ViewCategoryScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\QuickNavScreen' => 
    array (
      'id' => 'QuickNavScreen',
      'urlName' => 'quicknav',
      'urlPath' => 'quicknav',
      'title' => 'Quick navigation',
      'navigationTitle' => 'QuickNav',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\QuickNavScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\RevisionableScreen' => 
    array (
      'id' => 'RevisionableScreen',
      'urlName' => 'revisionable',
      'urlPath' => 'revisionable',
      'title' => 'Revisionables',
      'navigationTitle' => 'Revisionables',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\RevisionableScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
      ),
    ),
    'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen' => 
    array (
      'id' => 'RevisionableListScreen',
      'urlName' => 'list',
      'urlPath' => 'revisionable.list',
      'title' => 'Available revisionables',
      'navigationTitle' => 'Overview',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
      'path' => 'Area/RevisionableScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TagsScreen' => 
    array (
      'id' => 'TagsScreen',
      'urlName' => 'tags',
      'urlPath' => 'tags',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TagsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TagsScreen\\CreateTagScreen',
        1 => 'TestDriver\\Area\\TagsScreen\\TagListScreen',
        2 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen',
      ),
    ),
    'TestDriver\\Area\\TagsScreen\\CreateTagScreen' => 
    array (
      'id' => 'CreateTagScreen',
      'urlName' => 'create',
      'urlPath' => 'tags.create',
      'title' => 'Create a tag',
      'navigationTitle' => 'Create tag',
      'requiredRight' => 'CreateTags',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TagsScreen\\CreateTagScreen',
      'path' => 'Area/TagsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TagsScreen\\TagListScreen' => 
    array (
      'id' => 'TagListScreen',
      'urlName' => 'list',
      'urlPath' => 'tags.list',
      'title' => 'Available root tags',
      'navigationTitle' => 'List',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TagsScreen\\TagListScreen',
      'path' => 'Area/TagsScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TagsScreen\\ViewTagScreen' => 
    array (
      'id' => 'ViewTagScreen',
      'urlName' => 'view-tag',
      'urlPath' => 'tags.view-tag',
      'title' => 'View a tag',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen',
      'path' => 'Area/TagsScreen',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen',
        1 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen',
      ),
    ),
    'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen' => 
    array (
      'id' => 'TagSettingsScreen',
      'urlName' => 'tag-settings',
      'urlPath' => 'tags.view-tag.tag-settings',
      'title' => 'Edit tag settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
        'Edit the settings' => 'EditTags',
      ),
      'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen',
      'path' => 'Area/TagsScreen/ViewTagScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen' => 
    array (
      'id' => 'TagTreeScreen',
      'urlName' => 'tag-tree',
      'urlPath' => 'tags.view-tag.tag-tree',
      'title' => 'Tag tree',
      'navigationTitle' => 'Tree',
      'requiredRight' => 'EditTags',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen',
      'path' => 'Area/TagsScreen/ViewTagScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen' => 
    array (
      'id' => 'TestingScreen',
      'urlName' => 'testing',
      'urlPath' => 'testing',
      'title' => 'Testing',
      'navigationTitle' => 'Testing',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen',
        1 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen',
        2 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen',
        3 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen',
        4 => 'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen',
        5 => 'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen',
        6 => 'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen',
        7 => 'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen',
        8 => 'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen',
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen' => 
    array (
      'id' => 'CancelHandleActionsScreen',
      'urlName' => 'cancel-handle-actions',
      'urlPath' => 'testing.cancel-handle-actions',
      'title' => 'Cancel a screen\'s handling of actions',
      'navigationTitle' => 'Cancel a screen\'s handling of actions',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen' => 
    array (
      'id' => 'CollectionCreateBasicScreen',
      'urlName' => 'collection-create-basic',
      'urlPath' => 'testing.collection-create-basic',
      'title' => 'Create record - without settings manager',
      'navigationTitle' => 'Create record - without settings manager',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen' => 
    array (
      'id' => 'CollectionCreateManagerExtendedScreen',
      'urlName' => 'collection-create-manager-ex',
      'urlPath' => 'testing.collection-create-manager-ex',
      'title' => 'Create record - with extended settings manager',
      'navigationTitle' => 'Create record - with extended settings manager',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen' => 
    array (
      'id' => 'CollectionCreateManagerLegacyScreen',
      'urlName' => 'collection-create-legacy',
      'urlPath' => 'testing.collection-create-legacy',
      'title' => 'Create record - with settings manager',
      'navigationTitle' => 'Create record - with settings manager',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen' => 
    array (
      'id' => 'DBHelperSelectionTieInScreen',
      'urlName' => 'dbhelper-selection-tiein',
      'urlPath' => 'testing.dbhelper-selection-tiein',
      'title' => 'DBHelper selection tie-in',
      'navigationTitle' => 'DBHelper selection tie-in',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen' => 
    array (
      'id' => 'LogJavaScriptErrorScreen',
      'urlName' => 'log-javascript-error',
      'urlPath' => 'testing.log-javascript-error',
      'title' => 'Trigger a JavaScript error to test the error logging',
      'navigationTitle' => 'Trigger a JavaScript error to test the error logging',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen' => 
    array (
      'id' => 'ReplaceContentScreen',
      'urlName' => 'replace-content',
      'urlPath' => 'testing.replace-content',
      'title' => 'Replace screen content via the before render event',
      'navigationTitle' => 'Replace screen content via the before render event',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen' => 
    array (
      'id' => 'TestingOverviewScreen',
      'urlName' => 'overview',
      'urlPath' => 'testing.overview',
      'title' => 'Overview',
      'navigationTitle' => 'Overview',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen' => 
    array (
      'id' => 'TieInAncestryTestScreen',
      'urlName' => 'tiein-ancestry-test',
      'urlPath' => 'testing.tiein-ancestry-test',
      'title' => 'Tie-in ancestry',
      'navigationTitle' => 'Tie-in ancestry',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen',
      'path' => 'Area/TestingScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen' => 
    array (
      'id' => 'TimeTrackerScreen',
      'urlName' => 'time-tracker',
      'urlPath' => 'time-tracker',
      'title' => 'Time Tracker',
      'navigationTitle' => 'Time Tracker',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen',
        1 => 'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen',
        2 => 'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen',
        3 => 'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen',
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen' => 
    array (
      'id' => 'AutoFillScreen',
      'urlName' => 'auto-fill',
      'urlPath' => 'time-tracker.auto-fill',
      'title' => 'Auto-fill time entries',
      'navigationTitle' => 'Auto-fill',
      'requiredRight' => 'ViewTimeFilters',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen',
      'path' => 'Area/TimeTrackerScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen' => 
    array (
      'id' => 'CreateTimeSpanScreen',
      'urlName' => 'create-time-span',
      'urlPath' => 'time-tracker.create-time-span',
      'title' => 'Create a time span',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTimeFilters',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen',
      'path' => 'Area/TimeTrackerScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen' => 
    array (
      'id' => 'ExportScreen',
      'urlName' => 'export',
      'urlPath' => 'time-tracker.export',
      'title' => 'Export time entries',
      'navigationTitle' => 'Export',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen',
      'path' => 'Area/TimeTrackerScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen' => 
    array (
      'id' => 'ImportScreen',
      'urlName' => 'import',
      'urlPath' => 'time-tracker.import',
      'title' => 'Import time entries',
      'navigationTitle' => 'Import',
      'requiredRight' => 'EditTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen',
      'path' => 'Area/TimeTrackerScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\DayListScreen' => 
    array (
      'id' => 'DayListScreen',
      'urlName' => 'day',
      'urlPath' => 'day',
      'title' => 'Day view',
      'navigationTitle' => 'Day view',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\DayListScreen',
      'path' => 'Area/TimeTrackerScreen/ListScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalListScreen' => 
    array (
      'id' => 'GlobalListScreen',
      'urlName' => 'global',
      'urlPath' => 'global',
      'title' => 'Available time entries',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalListScreen',
      'path' => 'Area/TimeTrackerScreen/ListScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalSettingsScreen' => 
    array (
      'id' => 'GlobalSettingsScreen',
      'urlName' => 'time-settings',
      'urlPath' => 'time-settings',
      'title' => 'Global Settings',
      'navigationTitle' => 'Global Settings',
      'requiredRight' => 'EditTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalSettingsScreen',
      'path' => 'Area/TimeTrackerScreen/ListScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\TimeSpansListScreen' => 
    array (
      'id' => 'TimeSpansListScreen',
      'urlName' => 'time-spans-list',
      'urlPath' => 'time-spans-list',
      'title' => 'Time Spans',
      'navigationTitle' => 'Time Spans',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\TimeSpansListScreen',
      'path' => 'Area/TimeTrackerScreen/ListScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\TranslationsScreen' => 
    array (
      'id' => 'TranslationsScreen',
      'urlName' => 'translations',
      'urlPath' => 'translations',
      'title' => 'UI Translation tools',
      'navigationTitle' => 'Translation',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TranslationsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\UsersArea' => 
    array (
      'id' => 'UsersArea',
      'urlName' => 'users',
      'urlPath' => 'users',
      'title' => 'Users',
      'navigationTitle' => 'Users',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\UsersArea\\CreateUserMode',
        1 => 'TestDriver\\Area\\UsersArea\\UserListMode',
        2 => 'TestDriver\\Area\\UsersArea\\ViewUserMode',
      ),
    ),
    'TestDriver\\Area\\UsersArea\\CreateUserMode' => 
    array (
      'id' => 'CreateUserMode',
      'urlName' => 'create',
      'urlPath' => 'users.create',
      'title' => 'Create a new user',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'CreateUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea\\CreateUserMode',
      'path' => 'Area/UsersArea',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\UsersArea\\UserListMode' => 
    array (
      'id' => 'UserListMode',
      'urlName' => 'list',
      'urlPath' => 'users.list',
      'title' => 'Available users',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea\\UserListMode',
      'path' => 'Area/UsersArea',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\UsersArea\\ViewUserMode' => 
    array (
      'id' => 'ViewUserMode',
      'urlName' => 'view',
      'urlPath' => 'users.view',
      'title' => 'View user details',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode',
      'path' => 'Area/UsersArea',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode',
        1 => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode',
      ),
    ),
    'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode' => 
    array (
      'id' => 'UserSettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'users.view.settings',
      'title' => 'User settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'EditUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode',
      'path' => 'Area/UsersArea/ViewUserMode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode' => 
    array (
      'id' => 'UserStatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'users.view.status',
      'title' => 'User Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode',
      'path' => 'Area/UsersArea/ViewUserMode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver\\Area\\WelcomeScreen' => 
    array (
      'id' => 'WelcomeScreen',
      'urlName' => 'welcome',
      'urlPath' => 'welcome',
      'title' => 'Quickstart',
      'navigationTitle' => '',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\WelcomeScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\WelcomeScreen\\OverviewScreen',
      ),
    ),
    'TestDriver\\Area\\WelcomeScreen\\OverviewScreen' => 
    array (
      'id' => 'OverviewScreen',
      'urlName' => 'overview',
      'urlPath' => 'welcome.overview',
      'title' => 'Quickstart',
      'navigationTitle' => 'Quickstart',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\WelcomeScreen\\OverviewScreen',
      'path' => 'Area/WelcomeScreen',
      'subscreenClasses' => 
      array (
      ),
    ),
    'TestDriver_Area_WizardTest' => 
    array (
      'id' => 'WizardTest',
      'urlName' => 'wizardtest',
      'urlPath' => 'wizardtest',
      'title' => 'Test wizard',
      'navigationTitle' => 'Test wizard',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_WizardTest',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver_Area_WizardTest_Wizard',
      ),
    ),
    'TestDriver_Area_WizardTest_Wizard' => 
    array (
      'id' => 'Wizard',
      'urlName' => 'wizard',
      'urlPath' => 'wizardtest.wizard',
      'title' => 'Test wizard',
      'navigationTitle' => 'Test wizard',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_WizardTest_Wizard',
      'path' => 'Area/WizardTest',
      'subscreenClasses' => 
      array (
      ),
    ),
    'UI\\Admin\\Screens\\AppInterfaceDevelMode' => 
    array (
      'id' => 'AppInterfaceDevelMode',
      'urlName' => 'appinterface',
      'urlPath' => 'devel.appinterface',
      'title' => 'Application interface reference',
      'navigationTitle' => 'Interface refs',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/UI/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'UI\\Admin\\Screens\\CSSGenDevelMode' => 
    array (
      'id' => 'CSSGenDevelMode',
      'urlName' => 'css-gen',
      'urlPath' => 'devel.css-gen',
      'title' => 'CSS Generator',
      'navigationTitle' => 'CSS Generator',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'UI\\Admin\\Screens\\CSSGenDevelMode',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/UI/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
  ),
  'tree' => 
  array (
    'api-clients' => 
    array (
      'id' => 'APIClientsArea',
      'urlName' => 'api-clients',
      'urlPath' => 'api-clients',
      'title' => 'API Clients',
      'navigationTitle' => 'API Clients',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\APIClientsArea',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\APIClientsArea\\ClientsListMode',
        1 => 'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode',
        2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode',
      ),
      'subscreens' => 
      array (
        'list' => 
        array (
          'id' => 'ClientsListMode',
          'urlName' => 'list',
          'urlPath' => 'api-clients.list',
          'title' => 'Overview',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewAPIClients',
          'featureRights' => 
          array (
            'Multi-delete API Clients' => 'DeleteAPIClients',
          ),
          'class' => 'TestDriver\\Area\\APIClientsArea\\ClientsListMode',
          'path' => 'Area/APIClientsArea',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create' => 
        array (
          'id' => 'CreateAPIClientMode',
          'urlName' => 'create',
          'urlPath' => 'api-clients.create',
          'title' => 'Create a new API Client',
          'navigationTitle' => 'Create new client',
          'requiredRight' => 'CreateAPIClients',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\APIClientsArea\\CreateAPIClientMode',
          'path' => 'Area/APIClientsArea',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewAPIClientMode',
          'urlName' => 'view',
          'urlPath' => 'api-clients.view',
          'title' => 'View API Client',
          'navigationTitle' => 'View Client',
          'requiredRight' => 'ViewAPIClients',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode',
          'path' => 'Area/APIClientsArea',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode',
            1 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode',
            2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'APIClientSettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'api-clients.view.settings',
              'title' => 'API Client Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewAPIClients',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientSettingsSubmode',
              'path' => 'Area/APIClientsArea/ViewAPIClientMode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'APIClientStatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'api-clients.view.status',
              'title' => 'API Client Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewAPIClients',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIClientStatusSubmode',
              'path' => 'Area/APIClientsArea/ViewAPIClientMode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'api_keys' => 
            array (
              'id' => 'APIKeysSubmode',
              'urlName' => 'api_keys',
              'urlPath' => 'api-clients.view.api_keys',
              'title' => 'API keys',
              'navigationTitle' => 'API Keys',
              'requiredRight' => 'ViewAPIClients',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode',
              'path' => 'Area/APIClientsArea/ViewAPIClientMode',
              'subscreenClasses' => 
              array (
                0 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction',
                1 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction',
                2 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction',
                3 => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction',
              ),
              'subscreens' => 
              array (
                'settings' => 
                array (
                  'id' => 'APIKeySettingsAction',
                  'urlName' => 'settings',
                  'urlPath' => 'api-clients.view.api_keys.settings',
                  'title' => 'API Key Settings',
                  'navigationTitle' => 'Settings',
                  'requiredRight' => 'ViewAPIClients',
                  'featureRights' => 
                  array (
                    'Edit API Key Settings' => 'EditAPIClients',
                  ),
                  'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeySettingsAction',
                  'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
                  'subscreenClasses' => 
                  array (
                  ),
                  'subscreens' => 
                  array (
                  ),
                ),
                'list' => 
                array (
                  'id' => 'APIKeysListAction',
                  'urlName' => 'list',
                  'urlPath' => 'api-clients.view.api_keys.list',
                  'title' => 'Overview of API Keys',
                  'navigationTitle' => 'Overview',
                  'requiredRight' => NULL,
                  'featureRights' => NULL,
                  'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeysListAction',
                  'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
                  'subscreenClasses' => 
                  array (
                  ),
                  'subscreens' => 
                  array (
                  ),
                ),
                'status' => 
                array (
                  'id' => 'APIKeyStatusAction',
                  'urlName' => 'status',
                  'urlPath' => 'api-clients.view.api_keys.status',
                  'title' => 'API Key Status',
                  'navigationTitle' => 'Status',
                  'requiredRight' => 'ViewAPIClients',
                  'featureRights' => 
                  array (
                  ),
                  'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\APIKeyStatusAction',
                  'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
                  'subscreenClasses' => 
                  array (
                  ),
                  'subscreens' => 
                  array (
                  ),
                ),
                'create' => 
                array (
                  'id' => 'CreateAPIKeyAction',
                  'urlName' => 'create',
                  'urlPath' => 'api-clients.view.api_keys.create',
                  'title' => 'Create an API Key',
                  'navigationTitle' => 'Create new key',
                  'requiredRight' => NULL,
                  'featureRights' => NULL,
                  'class' => 'TestDriver\\Area\\APIClientsArea\\ViewAPIClientMode\\APIKeysSubmode\\CreateAPIKeyAction',
                  'path' => 'Area/APIClientsArea/ViewAPIClientMode/APIKeysSubmode',
                  'subscreenClasses' => 
                  array (
                  ),
                  'subscreens' => 
                  array (
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'media' => 
    array (
      'id' => 'MediaLibraryScreen',
      'urlName' => 'media',
      'urlPath' => 'media',
      'title' => 'Media library',
      'navigationTitle' => 'Media',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\MediaLibraryScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen',
        1 => 'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen',
        2 => 'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen',
        3 => 'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen',
        4 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen',
      ),
      'subscreens' => 
      array (
        'create' => 
        array (
          'id' => 'CreateMediaScreen',
          'urlName' => 'create',
          'urlPath' => 'media.create',
          'title' => 'Add a media file',
          'navigationTitle' => 'Add media',
          'requiredRight' => 'CreateMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\MediaLibraryScreen\\CreateMediaScreen',
          'path' => 'Area/MediaLibraryScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'image-gallery' => 
        array (
          'id' => 'ImageGalleryScreen',
          'urlName' => 'image-gallery',
          'urlPath' => 'media.image-gallery',
          'title' => 'Image gallery',
          'navigationTitle' => 'Image gallery',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ImageGalleryScreen',
          'path' => 'Area/MediaLibraryScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'MediaListScreen',
          'urlName' => 'list',
          'urlPath' => 'media.list',
          'title' => 'Available media files',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaListScreen',
          'path' => 'Area/MediaLibraryScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'settings' => 
        array (
          'id' => 'MediaSettingsScreen',
          'urlName' => 'settings',
          'urlPath' => 'media.settings',
          'title' => 'Media settings',
          'navigationTitle' => 'Media settings',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\MediaLibraryScreen\\MediaSettingsScreen',
          'path' => 'Area/MediaLibraryScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewMediaScreen',
          'urlName' => 'view',
          'urlPath' => 'media.view',
          'title' => 'Media file',
          'navigationTitle' => 'Media file',
          'requiredRight' => 'ViewMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen',
          'path' => 'Area/MediaLibraryScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen',
            1 => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen',
          ),
          'subscreens' => 
          array (
            'status' => 
            array (
              'id' => 'MediaStatusScreen',
              'urlName' => 'status',
              'urlPath' => 'media.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewMedia',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaStatusScreen',
              'path' => 'Area/MediaLibraryScreen/ViewMediaScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'tagging' => 
            array (
              'id' => 'MediaTagsScreen',
              'urlName' => 'tagging',
              'urlPath' => 'media.view.tagging',
              'title' => 'Tags',
              'navigationTitle' => 'Tags',
              'requiredRight' => 'EditMedia',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\MediaLibraryScreen\\ViewMediaScreen\\MediaTagsScreen',
              'path' => 'Area/MediaLibraryScreen/ViewMediaScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'countries' => 
    array (
      'id' => 'CountriesScreen',
      'urlName' => 'countries',
      'urlPath' => 'countries',
      'title' => 'Countries',
      'navigationTitle' => 'Countries',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\CountriesScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\CountriesScreen\\CreateScreen',
        1 => 'TestDriver\\Area\\CountriesScreen\\ListScreen',
        2 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen',
      ),
      'subscreens' => 
      array (
        'create' => 
        array (
          'id' => 'CreateScreen',
          'urlName' => 'create',
          'urlPath' => 'countries.create',
          'title' => 'Create a new country',
          'navigationTitle' => 'Create a country',
          'requiredRight' => 'CreateCountries',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\CountriesScreen\\CreateScreen',
          'path' => 'Area/CountriesScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'ListScreen',
          'urlName' => 'list',
          'urlPath' => 'countries.list',
          'title' => 'Available countries',
          'navigationTitle' => 'List',
          'requiredRight' => 'ViewCountries',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\CountriesScreen\\ListScreen',
          'path' => 'Area/CountriesScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewScreen',
          'urlName' => 'view',
          'urlPath' => 'countries.view',
          'title' => 'View a country',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewCountries',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen',
          'path' => 'Area/CountriesScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen',
            1 => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'SettingsScreen',
              'urlName' => 'settings',
              'urlPath' => 'countries.view.settings',
              'title' => 'Country settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'EditCountries',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\SettingsScreen',
              'path' => 'Area/CountriesScreen/ViewScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'StatusScreen',
              'urlName' => 'status',
              'urlPath' => 'countries.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewCountries',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\CountriesScreen\\ViewScreen\\StatusScreen',
              'path' => 'Area/CountriesScreen/ViewScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'devel' => 
    array (
      'id' => 'DevelArea',
      'urlName' => 'devel',
      'urlPath' => 'devel',
      'title' => 'Developer tools',
      'navigationTitle' => 'Developer tools',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Development\\Admin\\Screens\\DevelArea',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
        1 => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
        2 => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
        3 => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
        4 => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
        5 => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
        6 => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
        7 => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
        8 => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
        9 => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
        10 => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
        11 => 'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode',
        12 => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
        13 => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
        14 => 'UI\\Admin\\Screens\\CSSGenDevelMode',
      ),
      'subscreens' => 
      array (
        'appconfig' => 
        array (
          'id' => 'AppConfigMode',
          'urlName' => 'appconfig',
          'urlPath' => 'devel.appconfig',
          'title' => 'Application configuration',
          'navigationTitle' => 'Configuration',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Environments/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'appinterface' => 
        array (
          'id' => 'AppInterfaceDevelMode',
          'urlName' => 'appinterface',
          'urlPath' => 'devel.appinterface',
          'title' => 'Application interface reference',
          'navigationTitle' => 'Interface refs',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/UI/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'appsets' => 
        array (
          'id' => 'ApplicationSetsMode',
          'urlName' => 'appsets',
          'urlPath' => 'devel.appsets',
          'title' => 'Application interface sets',
          'navigationTitle' => 'Appsets',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Sets\\Admin\\Screens\\CreateSetSubmode',
            1 => 'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode',
            2 => 'Application\\Sets\\Admin\\Screens\\EditSetSubmode',
            3 => 'Application\\Sets\\Admin\\Screens\\SetsListSubmode',
          ),
          'subscreens' => 
          array (
            'create' => 
            array (
              'id' => 'CreateSetSubmode',
              'urlName' => 'create',
              'urlPath' => 'devel.appsets.create',
              'title' => 'Create a new application set',
              'navigationTitle' => 'Create new set',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Sets\\Admin\\Screens\\CreateSetSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'delete' => 
            array (
              'id' => 'DeleteSetSubmode',
              'urlName' => 'delete',
              'urlPath' => 'devel.appsets.delete',
              'title' => 'Delete an application set',
              'navigationTitle' => 'Delete set',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Sets\\Admin\\Screens\\DeleteSetSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'edit' => 
            array (
              'id' => 'EditSetSubmode',
              'urlName' => 'edit',
              'urlPath' => 'devel.appsets.edit',
              'title' => 'Create a new application set',
              'navigationTitle' => 'Create new set',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Sets\\Admin\\Screens\\EditSetSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'list' => 
            array (
              'id' => 'SetsListSubmode',
              'urlName' => 'list',
              'urlPath' => 'devel.appsets.list',
              'title' => 'List of application sets',
              'navigationTitle' => 'List',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Sets\\Admin\\Screens\\SetsListSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Sets/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'appsettings' => 
        array (
          'id' => 'AppSettingsDevelMode',
          'urlName' => 'appsettings',
          'urlPath' => 'devel.appsettings',
          'title' => 'Application settings',
          'navigationTitle' => 'Application settings',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/AppSettings/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'cache-control' => 
        array (
          'id' => 'CacheControlMode',
          'urlName' => 'cache-control',
          'urlPath' => 'devel.cache-control',
          'title' => 'Cache control',
          'navigationTitle' => 'Cache control',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/CacheControl/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'css-gen' => 
        array (
          'id' => 'CSSGenDevelMode',
          'urlName' => 'css-gen',
          'urlPath' => 'devel.css-gen',
          'title' => 'CSS Generator',
          'navigationTitle' => 'CSS Generator',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'UI\\Admin\\Screens\\CSSGenDevelMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/UI/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'dbdump' => 
        array (
          'id' => 'DatabaseDumpDevMode',
          'urlName' => 'dbdump',
          'urlPath' => 'devel.dbdump',
          'title' => 'Database dumps',
          'navigationTitle' => 'Database dumps',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'deploy-history' => 
        array (
          'id' => 'DeploymentHistoryMode',
          'urlName' => 'deploy-history',
          'urlPath' => 'devel.deploy-history',
          'title' => 'Deployment history',
          'navigationTitle' => 'Deployment history',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/DeploymentRegistry/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'overview' => 
        array (
          'id' => 'DevelOverviewMode',
          'urlName' => 'overview',
          'urlPath' => 'devel.overview',
          'title' => 'Developer tools overview',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Development/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'errorlog' => 
        array (
          'id' => 'ErrorLogMode',
          'urlName' => 'errorlog',
          'urlPath' => 'devel.errorlog',
          'title' => 'Error log',
          'navigationTitle' => 'Error log',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
            1 => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
          ),
          'subscreens' => 
          array (
            'list' => 
            array (
              'id' => 'ListSubmode',
              'urlName' => 'list',
              'urlPath' => 'devel.errorlog.list',
              'title' => 'Error Log Entries',
              'navigationTitle' => 'List',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'view' => 
            array (
              'id' => 'ViewSubmode',
              'urlName' => 'view',
              'urlPath' => 'devel.errorlog.view',
              'title' => 'View error log',
              'navigationTitle' => 'View',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/ErrorLog/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'maintenance' => 
        array (
          'id' => 'MaintenanceMode',
          'urlName' => 'maintenance',
          'urlPath' => 'devel.maintenance',
          'title' => 'Planned maintenance',
          'navigationTitle' => 'Maintenance',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Maintenance/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
          ),
          'subscreens' => 
          array (
            'create' => 
            array (
              'id' => 'CreateSubmode',
              'urlName' => 'create',
              'urlPath' => 'devel.maintenance.create',
              'title' => 'Create maintenance plan',
              'navigationTitle' => 'Create plan',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Maintenance/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'messagelog' => 
        array (
          'id' => 'MessageLogDevelMode',
          'urlName' => 'messagelog',
          'urlPath' => 'devel.messagelog',
          'title' => 'Application messagelog',
          'navigationTitle' => 'Messagelog',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Messagelogs/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'renamer' => 
        array (
          'id' => 'RenamerMode',
          'urlName' => 'renamer',
          'urlPath' => 'devel.renamer',
          'title' => 'Database Renamer',
          'navigationTitle' => 'DB Renamer',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
            1 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
            2 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
            3 => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
            4 => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
          ),
          'subscreens' => 
          array (
            'configuration' => 
            array (
              'id' => 'ConfigurationSubmode',
              'urlName' => 'configuration',
              'urlPath' => 'devel.renamer.configuration',
              'title' => 'Configuration',
              'navigationTitle' => 'Configuration',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'export' => 
            array (
              'id' => 'ExportSubmode',
              'urlName' => 'export',
              'urlPath' => 'devel.renamer.export',
              'title' => 'Export',
              'navigationTitle' => 'Export',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'replace' => 
            array (
              'id' => 'ReplaceSubmode',
              'urlName' => 'replace',
              'urlPath' => 'devel.renamer.replace',
              'title' => 'Replace',
              'navigationTitle' => 'Replace',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'results' => 
            array (
              'id' => 'ResultsSubmode',
              'urlName' => 'results',
              'urlPath' => 'devel.renamer.results',
              'title' => 'Results',
              'navigationTitle' => 'Results',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'search' => 
            array (
              'id' => 'SearchSubmode',
              'urlName' => 'search',
              'urlPath' => 'devel.renamer.search',
              'title' => 'Search',
              'navigationTitle' => 'Search',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'rightsoverview' => 
        array (
          'id' => 'RightsOverviewDevelMode',
          'urlName' => 'rightsoverview',
          'urlPath' => 'devel.rightsoverview',
          'title' => 'User rights overview',
          'navigationTitle' => 'User rights',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Users\\Admin\\Screens\\Mode\\RightsOverviewDevelMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Users/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'whatsneweditor' => 
        array (
          'id' => 'WhatsNewEditorMode',
          'urlName' => 'whatsneweditor',
          'urlPath' => 'devel.whatsneweditor',
          'title' => 'What\'s new editor',
          'navigationTitle' => 'What\'s new editor',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
          'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/WhatsNew/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
          ),
          'subscreens' => 
          array (
            'edit' => 
            array (
              'id' => 'EditSubmode',
              'urlName' => 'edit',
              'urlPath' => 'devel.whatsneweditor.edit',
              'title' => 'Edit a version',
              'navigationTitle' => 'Edit',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
              'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/WhatsNew/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'news' => 
    array (
      'id' => 'NewsScreen',
      'urlName' => 'news',
      'urlPath' => 'news',
      'title' => 'Application news central',
      'navigationTitle' => 'News central',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\NewsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\NewsScreen\\CategoriesListScreen',
        1 => 'TestDriver\\Area\\NewsScreen\\CreateAlertScreen',
        2 => 'TestDriver\\Area\\NewsScreen\\CreateArticleScreen',
        3 => 'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen',
        4 => 'TestDriver\\Area\\NewsScreen\\NewsListScreen',
        5 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen',
        6 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen',
        7 => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen',
      ),
      'subscreens' => 
      array (
        'categories-list' => 
        array (
          'id' => 'CategoriesListScreen',
          'urlName' => 'categories-list',
          'urlPath' => 'news.categories-list',
          'title' => 'Available categories',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\CategoriesListScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create-alert' => 
        array (
          'id' => 'CreateAlertScreen',
          'urlName' => 'create-alert',
          'urlPath' => 'news.create-alert',
          'title' => 'Create a news alert',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'CreateAlerts',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\CreateAlertScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create-article' => 
        array (
          'id' => 'CreateArticleScreen',
          'urlName' => 'create-article',
          'urlPath' => 'news.create-article',
          'title' => 'Create a news article',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'CreateNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\CreateArticleScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create-category' => 
        array (
          'id' => 'CreateCategoryScreen',
          'urlName' => 'create-category',
          'urlPath' => 'news.create-category',
          'title' => 'Create a news category',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'EditNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\CreateCategoryScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'NewsListScreen',
          'urlName' => 'list',
          'urlPath' => 'news.list',
          'title' => 'Available news articles',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\NewsListScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'read' => 
        array (
          'id' => 'ReadNewsScreen',
          'urlName' => 'read',
          'urlPath' => 'news.read',
          'title' => 'AppTestSuite news',
          'navigationTitle' => 'News',
          'requiredRight' => 'Login',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen',
            1 => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen',
          ),
          'subscreens' => 
          array (
            'article' => 
            array (
              'id' => 'ReadArticleScreen',
              'urlName' => 'article',
              'urlPath' => 'news.read.article',
              'title' => 'News Article',
              'navigationTitle' => 'Article',
              'requiredRight' => 'Login',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticleScreen',
              'path' => 'Area/NewsScreen/ReadNewsScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'articles' => 
            array (
              'id' => 'ReadArticlesScreen',
              'urlName' => 'articles',
              'urlPath' => 'news.read.articles',
              'title' => 'AppTestSuite news',
              'navigationTitle' => 'News',
              'requiredRight' => 'Login',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\NewsScreen\\ReadNewsScreen\\ReadArticlesScreen',
              'path' => 'Area/NewsScreen/ReadNewsScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewArticleScreen',
          'urlName' => 'view',
          'urlPath' => 'news.view',
          'title' => 'View news entry',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen',
            1 => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'ArticleSettingsScreen',
              'urlName' => 'settings',
              'urlPath' => 'news.view.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
                'Modify the settings' => 'EditNews',
              ),
              'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleSettingsScreen',
              'path' => 'Area/NewsScreen/ViewArticleScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'ArticleStatusScreen',
              'urlName' => 'status',
              'urlPath' => 'news.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\NewsScreen\\ViewArticleScreen\\ArticleStatusScreen',
              'path' => 'Area/NewsScreen/ViewArticleScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'view-category' => 
        array (
          'id' => 'ViewCategoryScreen',
          'urlName' => 'view-category',
          'urlPath' => 'news.view-category',
          'title' => 'View news category',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen',
          'path' => 'Area/NewsScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'CategorySettingsScreen',
              'urlName' => 'settings',
              'urlPath' => 'news.view-category.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
                'Modify the settings' => 'EditNews',
              ),
              'class' => 'TestDriver\\Area\\NewsScreen\\ViewCategoryScreen\\CategorySettingsScreen',
              'path' => 'Area/NewsScreen/ViewCategoryScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'quicknav' => 
    array (
      'id' => 'QuickNavScreen',
      'urlName' => 'quicknav',
      'urlPath' => 'quicknav',
      'title' => 'Quick navigation',
      'navigationTitle' => 'QuickNav',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\QuickNavScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
      ),
      'subscreens' => 
      array (
      ),
    ),
    'revisionable' => 
    array (
      'id' => 'RevisionableScreen',
      'urlName' => 'revisionable',
      'urlPath' => 'revisionable',
      'title' => 'Revisionables',
      'navigationTitle' => 'Revisionables',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\RevisionableScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
      ),
      'subscreens' => 
      array (
        'list' => 
        array (
          'id' => 'RevisionableListScreen',
          'urlName' => 'list',
          'urlPath' => 'revisionable.list',
          'title' => 'Available revisionables',
          'navigationTitle' => 'Overview',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
          'path' => 'Area/RevisionableScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'settings' => 
    array (
      'id' => 'Settings',
      'urlName' => 'settings',
      'urlPath' => 'settings',
      'title' => 'User settings',
      'navigationTitle' => 'User settings',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application_Admin_Area_Settings',
      'path' => 'Users/smordziol/Webserver/libraries/application-framework/src/classes/Application/Admin/Area',
      'subscreenClasses' => 
      array (
      ),
      'subscreens' => 
      array (
      ),
    ),
    'tags' => 
    array (
      'id' => 'TagsScreen',
      'urlName' => 'tags',
      'urlPath' => 'tags',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TagsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TagsScreen\\CreateTagScreen',
        1 => 'TestDriver\\Area\\TagsScreen\\TagListScreen',
        2 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen',
      ),
      'subscreens' => 
      array (
        'create' => 
        array (
          'id' => 'CreateTagScreen',
          'urlName' => 'create',
          'urlPath' => 'tags.create',
          'title' => 'Create a tag',
          'navigationTitle' => 'Create tag',
          'requiredRight' => 'CreateTags',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TagsScreen\\CreateTagScreen',
          'path' => 'Area/TagsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'TagListScreen',
          'urlName' => 'list',
          'urlPath' => 'tags.list',
          'title' => 'Available root tags',
          'navigationTitle' => 'List',
          'requiredRight' => 'ViewTags',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TagsScreen\\TagListScreen',
          'path' => 'Area/TagsScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view-tag' => 
        array (
          'id' => 'ViewTagScreen',
          'urlName' => 'view-tag',
          'urlPath' => 'tags.view-tag',
          'title' => 'View a tag',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewTags',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen',
          'path' => 'Area/TagsScreen',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen',
            1 => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen',
          ),
          'subscreens' => 
          array (
            'tag-settings' => 
            array (
              'id' => 'TagSettingsScreen',
              'urlName' => 'tag-settings',
              'urlPath' => 'tags.view-tag.tag-settings',
              'title' => 'Edit tag settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewTags',
              'featureRights' => 
              array (
                'Edit the settings' => 'EditTags',
              ),
              'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagSettingsScreen',
              'path' => 'Area/TagsScreen/ViewTagScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'tag-tree' => 
            array (
              'id' => 'TagTreeScreen',
              'urlName' => 'tag-tree',
              'urlPath' => 'tags.view-tag.tag-tree',
              'title' => 'Tag tree',
              'navigationTitle' => 'Tree',
              'requiredRight' => 'EditTags',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\TagsScreen\\ViewTagScreen\\TagTreeScreen',
              'path' => 'Area/TagsScreen/ViewTagScreen',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'testing' => 
    array (
      'id' => 'TestingScreen',
      'urlName' => 'testing',
      'urlPath' => 'testing',
      'title' => 'Testing',
      'navigationTitle' => 'Testing',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TestingScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen',
        1 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen',
        2 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen',
        3 => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen',
        4 => 'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen',
        5 => 'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen',
        6 => 'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen',
        7 => 'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen',
        8 => 'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen',
      ),
      'subscreens' => 
      array (
        'cancel-handle-actions' => 
        array (
          'id' => 'CancelHandleActionsScreen',
          'urlName' => 'cancel-handle-actions',
          'urlPath' => 'testing.cancel-handle-actions',
          'title' => 'Cancel a screen\'s handling of actions',
          'navigationTitle' => 'Cancel a screen\'s handling of actions',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\CancelHandleActionsScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'collection-create-basic' => 
        array (
          'id' => 'CollectionCreateBasicScreen',
          'urlName' => 'collection-create-basic',
          'urlPath' => 'testing.collection-create-basic',
          'title' => 'Create record - without settings manager',
          'navigationTitle' => 'Create record - without settings manager',
          'requiredRight' => NULL,
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateBasicScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'collection-create-manager-ex' => 
        array (
          'id' => 'CollectionCreateManagerExtendedScreen',
          'urlName' => 'collection-create-manager-ex',
          'urlPath' => 'testing.collection-create-manager-ex',
          'title' => 'Create record - with extended settings manager',
          'navigationTitle' => 'Create record - with extended settings manager',
          'requiredRight' => NULL,
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerExtendedScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'collection-create-legacy' => 
        array (
          'id' => 'CollectionCreateManagerLegacyScreen',
          'urlName' => 'collection-create-legacy',
          'urlPath' => 'testing.collection-create-legacy',
          'title' => 'Create record - with settings manager',
          'navigationTitle' => 'Create record - with settings manager',
          'requiredRight' => NULL,
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TestingScreen\\CollectionCreateManagerLegacyScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'dbhelper-selection-tiein' => 
        array (
          'id' => 'DBHelperSelectionTieInScreen',
          'urlName' => 'dbhelper-selection-tiein',
          'urlPath' => 'testing.dbhelper-selection-tiein',
          'title' => 'DBHelper selection tie-in',
          'navigationTitle' => 'DBHelper selection tie-in',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\DBHelperSelectionTieInScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'log-javascript-error' => 
        array (
          'id' => 'LogJavaScriptErrorScreen',
          'urlName' => 'log-javascript-error',
          'urlPath' => 'testing.log-javascript-error',
          'title' => 'Trigger a JavaScript error to test the error logging',
          'navigationTitle' => 'Trigger a JavaScript error to test the error logging',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\LogJavaScriptErrorScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'replace-content' => 
        array (
          'id' => 'ReplaceContentScreen',
          'urlName' => 'replace-content',
          'urlPath' => 'testing.replace-content',
          'title' => 'Replace screen content via the before render event',
          'navigationTitle' => 'Replace screen content via the before render event',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\ReplaceContentScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'overview' => 
        array (
          'id' => 'TestingOverviewScreen',
          'urlName' => 'overview',
          'urlPath' => 'testing.overview',
          'title' => 'Overview',
          'navigationTitle' => 'Overview',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\TestingOverviewScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'tiein-ancestry-test' => 
        array (
          'id' => 'TieInAncestryTestScreen',
          'urlName' => 'tiein-ancestry-test',
          'urlPath' => 'testing.tiein-ancestry-test',
          'title' => 'Tie-in ancestry',
          'navigationTitle' => 'Tie-in ancestry',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\TestingScreen\\TieInAncestryTestScreen',
          'path' => 'Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'time-tracker' => 
    array (
      'id' => 'TimeTrackerScreen',
      'urlName' => 'time-tracker',
      'urlPath' => 'time-tracker',
      'title' => 'Time Tracker',
      'navigationTitle' => 'Time Tracker',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\TimeTrackerScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen',
        1 => 'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen',
        2 => 'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen',
        3 => 'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen',
      ),
      'subscreens' => 
      array (
        'auto-fill' => 
        array (
          'id' => 'AutoFillScreen',
          'urlName' => 'auto-fill',
          'urlPath' => 'time-tracker.auto-fill',
          'title' => 'Auto-fill time entries',
          'navigationTitle' => 'Auto-fill',
          'requiredRight' => 'ViewTimeFilters',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TimeTrackerScreen\\AutoFillScreen',
          'path' => 'Area/TimeTrackerScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create-time-span' => 
        array (
          'id' => 'CreateTimeSpanScreen',
          'urlName' => 'create-time-span',
          'urlPath' => 'time-tracker.create-time-span',
          'title' => 'Create a time span',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'ViewTimeFilters',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TimeTrackerScreen\\CreateTimeSpanScreen',
          'path' => 'Area/TimeTrackerScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'export' => 
        array (
          'id' => 'ExportScreen',
          'urlName' => 'export',
          'urlPath' => 'time-tracker.export',
          'title' => 'Export time entries',
          'navigationTitle' => 'Export',
          'requiredRight' => 'ViewTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ExportScreen',
          'path' => 'Area/TimeTrackerScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'import' => 
        array (
          'id' => 'ImportScreen',
          'urlName' => 'import',
          'urlPath' => 'time-tracker.import',
          'title' => 'Import time entries',
          'navigationTitle' => 'Import',
          'requiredRight' => 'EditTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\TimeTrackerScreen\\ImportScreen',
          'path' => 'Area/TimeTrackerScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'translations' => 
    array (
      'id' => 'TranslationsScreen',
      'urlName' => 'translations',
      'urlPath' => 'translations',
      'title' => 'UI Translation tools',
      'navigationTitle' => 'Translation',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\TranslationsScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
      ),
      'subscreens' => 
      array (
      ),
    ),
    'users' => 
    array (
      'id' => 'UsersArea',
      'urlName' => 'users',
      'urlPath' => 'users',
      'title' => 'Users',
      'navigationTitle' => 'Users',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\UsersArea',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\UsersArea\\CreateUserMode',
        1 => 'TestDriver\\Area\\UsersArea\\UserListMode',
        2 => 'TestDriver\\Area\\UsersArea\\ViewUserMode',
      ),
      'subscreens' => 
      array (
        'create' => 
        array (
          'id' => 'CreateUserMode',
          'urlName' => 'create',
          'urlPath' => 'users.create',
          'title' => 'Create a new user',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'CreateUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\UsersArea\\CreateUserMode',
          'path' => 'Area/UsersArea',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'UserListMode',
          'urlName' => 'list',
          'urlPath' => 'users.list',
          'title' => 'Available users',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\UsersArea\\UserListMode',
          'path' => 'Area/UsersArea',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewUserMode',
          'urlName' => 'view',
          'urlPath' => 'users.view',
          'title' => 'View user details',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode',
          'path' => 'Area/UsersArea',
          'subscreenClasses' => 
          array (
            0 => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode',
            1 => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'UserSettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'users.view.settings',
              'title' => 'User settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'EditUsers',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserSettingsSubmode',
              'path' => 'Area/UsersArea/ViewUserMode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'UserStatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'users.view.status',
              'title' => 'User Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewUsers',
              'featureRights' => 
              array (
              ),
              'class' => 'TestDriver\\Area\\UsersArea\\ViewUserMode\\UserStatusSubmode',
              'path' => 'Area/UsersArea/ViewUserMode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    'welcome' => 
    array (
      'id' => 'WelcomeScreen',
      'urlName' => 'welcome',
      'urlPath' => 'welcome',
      'title' => 'Quickstart',
      'navigationTitle' => '',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\WelcomeScreen',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver\\Area\\WelcomeScreen\\OverviewScreen',
      ),
      'subscreens' => 
      array (
        'overview' => 
        array (
          'id' => 'OverviewScreen',
          'urlName' => 'overview',
          'urlPath' => 'welcome.overview',
          'title' => 'Quickstart',
          'navigationTitle' => 'Quickstart',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\WelcomeScreen\\OverviewScreen',
          'path' => 'Area/WelcomeScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'wizardtest' => 
    array (
      'id' => 'WizardTest',
      'urlName' => 'wizardtest',
      'urlPath' => 'wizardtest',
      'title' => 'Test wizard',
      'navigationTitle' => 'Test wizard',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_WizardTest',
      'path' => 'Area',
      'subscreenClasses' => 
      array (
        0 => 'TestDriver_Area_WizardTest_Wizard',
      ),
      'subscreens' => 
      array (
        'wizard' => 
        array (
          'id' => 'Wizard',
          'urlName' => 'wizard',
          'urlPath' => 'wizardtest.wizard',
          'title' => 'Test wizard',
          'navigationTitle' => 'Test wizard',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_WizardTest_Wizard',
          'path' => 'Area/WizardTest',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
  ),
);
