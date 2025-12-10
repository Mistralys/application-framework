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
    'css-gen' => 'TestDriver\\Area\\CSSGenScreen',
    'day' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\DayListScreen',
    'devel' => 'TestDriver_Area_Devel',
    'devel.appconfig' => 'TestDriver\\Area\\Devel\\AppConfigScreen',
    'devel.appinterface' => 'TestDriver_Area_Devel_Appinterface',
    'devel.appsets' => 'TestDriver\\Area\\Devel\\AppSetsScreen',
    'devel.appsets.create' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\CreateAppSetScreen',
    'devel.appsets.delete' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\DeleteAppSetScreen',
    'devel.appsets.edit' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\EditAppSetScreen',
    'devel.appsets.list' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\AppSetsListScreen',
    'devel.appsettings' => 'TestDriver_Area_Devel_AppSettings',
    'devel.cache-control' => 'TestDriver\\Area\\Devel\\CacheControlSceen',
    'devel.deploy-history' => 'TestDriver\\Area\\Devel\\DeploymentHistoryScreen',
    'devel.errorlog' => 'TestDriver_Area_Devel_Errorlog',
    'devel.errorlog.list' => 'TestDriver_Area_Devel_Errorlog_List',
    'devel.errorlog.view' => 'TestDriver_Area_Devel_Errorlog_View',
    'devel.maintenance' => 'TestDriver_Area_Devel_Maintenance',
    'devel.maintenance.create' => 'TestDriver_Area_Devel_Maintenance_Create',
    'devel.messagelog' => 'TestDriver_Area_Devel_Messagelog',
    'devel.overview' => 'TestDriver_Area_Devel_Overview',
    'devel.renamer' => 'TestDriver\\Area\\Devel\\RenamerMode',
    'devel.renamer.configuration' => 'TestDriver\\Area\\Devel\\RenamerMode\\ConfigurationSubmode',
    'devel.renamer.export' => 'TestDriver\\Area\\Devel\\RenamerMode\\ExportSubmode',
    'devel.renamer.replace' => 'TestDriver\\Area\\Devel\\RenamerMode\\ReplaceSubmode',
    'devel.renamer.results' => 'TestDriver\\Area\\Devel\\RenamerMode\\ResultsSubmode',
    'devel.renamer.search' => 'TestDriver\\Area\\Devel\\RenamerMode\\SearchSubmode',
    'devel.rightsoverview' => 'TestDriver_Area_Devel_RightsOverview',
    'devel.users' => 'TestDriver\\Area\\Devel\\UsersScreen',
    'devel.whatsneweditor' => 'TestDriver_Area_Devel_WhatsNewEditor',
    'devel.whatsneweditor.edit' => 'TestDriver_Area_Devel_WhatsNewEditor_Edit',
    'global' => 'TestDriver\\Area\\TimeTrackerScreen\\ListScreen\\GlobalListScreen',
    'list' => 'TestDriver\\Area\\Devel\\Users\\UsersList',
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
    'settings' => 'TestDriver_Area_Settings',
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
    ),
    'TestDriver\\Area\\CSSGenScreen' => 
    array (
      'id' => 'CSSGenScreen',
      'urlName' => 'css-gen',
      'urlPath' => 'css-gen',
      'title' => 'CSS Generator',
      'navigationTitle' => 'CSS Generator',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\CSSGenScreen',
      'path' => 'Area',
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
    ),
    'TestDriver\\Area\\Devel\\AppConfigScreen' => 
    array (
      'id' => 'AppConfigScreen',
      'urlName' => 'appconfig',
      'urlPath' => 'devel.appconfig',
      'title' => 'Application configuration',
      'navigationTitle' => 'Configuration',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppConfigScreen',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\AppSetsScreen' => 
    array (
      'id' => 'AppSetsScreen',
      'urlName' => 'appsets',
      'urlPath' => 'devel.appsets',
      'title' => 'Application interface sets',
      'navigationTitle' => 'Appsets',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\AppSetsScreen\\AppSetsListScreen' => 
    array (
      'id' => 'AppSetsListScreen',
      'urlName' => 'list',
      'urlPath' => 'devel.appsets.list',
      'title' => 'List of application sets',
      'navigationTitle' => 'List',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\AppSetsListScreen',
      'path' => 'Area/Devel/AppSetsScreen',
    ),
    'TestDriver\\Area\\Devel\\AppSetsScreen\\CreateAppSetScreen' => 
    array (
      'id' => 'CreateAppSetScreen',
      'urlName' => 'create',
      'urlPath' => 'devel.appsets.create',
      'title' => 'Create a new application set',
      'navigationTitle' => 'Create new set',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\CreateAppSetScreen',
      'path' => 'Area/Devel/AppSetsScreen',
    ),
    'TestDriver\\Area\\Devel\\AppSetsScreen\\DeleteAppSetScreen' => 
    array (
      'id' => 'DeleteAppSetScreen',
      'urlName' => 'delete',
      'urlPath' => 'devel.appsets.delete',
      'title' => 'Delete an application set',
      'navigationTitle' => 'Delete set',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\DeleteAppSetScreen',
      'path' => 'Area/Devel/AppSetsScreen',
    ),
    'TestDriver\\Area\\Devel\\AppSetsScreen\\EditAppSetScreen' => 
    array (
      'id' => 'EditAppSetScreen',
      'urlName' => 'edit',
      'urlPath' => 'devel.appsets.edit',
      'title' => 'Create a new application set',
      'navigationTitle' => 'Create new set',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\EditAppSetScreen',
      'path' => 'Area/Devel/AppSetsScreen',
    ),
    'TestDriver\\Area\\Devel\\CacheControlSceen' => 
    array (
      'id' => 'CacheControlSceen',
      'urlName' => 'cache-control',
      'urlPath' => 'devel.cache-control',
      'title' => 'Cache control',
      'navigationTitle' => 'Cache control',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\CacheControlSceen',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\DeploymentHistoryScreen' => 
    array (
      'id' => 'DeploymentHistoryScreen',
      'urlName' => 'deploy-history',
      'urlPath' => 'devel.deploy-history',
      'title' => 'Deployment history',
      'navigationTitle' => 'Deployment history',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\DeploymentHistoryScreen',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode\\ConfigurationSubmode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ConfigurationSubmode',
      'path' => 'Area/Devel/RenamerMode',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode\\ExportSubmode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ExportSubmode',
      'path' => 'Area/Devel/RenamerMode',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode\\ReplaceSubmode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ReplaceSubmode',
      'path' => 'Area/Devel/RenamerMode',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode\\ResultsSubmode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ResultsSubmode',
      'path' => 'Area/Devel/RenamerMode',
    ),
    'TestDriver\\Area\\Devel\\RenamerMode\\SearchSubmode' => 
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
      'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\SearchSubmode',
      'path' => 'Area/Devel/RenamerMode',
    ),
    'TestDriver\\Area\\Devel\\UsersScreen' => 
    array (
      'id' => 'UsersScreen',
      'urlName' => 'users',
      'urlPath' => 'devel.users',
      'title' => 'Users management',
      'navigationTitle' => 'Users',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver\\Area\\Devel\\UsersScreen',
      'path' => 'Area/Devel',
    ),
    'TestDriver\\Area\\Devel\\Users\\UsersList' => 
    array (
      'id' => 'UsersList',
      'urlName' => 'list',
      'urlPath' => 'list',
      'title' => 'Users list',
      'navigationTitle' => 'List',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\Devel\\Users\\UsersList',
      'path' => 'Area/Devel/Users',
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
    ),
    'TestDriver_Area_Devel' => 
    array (
      'id' => 'Devel',
      'urlName' => 'devel',
      'urlPath' => 'devel',
      'title' => 'Developer tools',
      'navigationTitle' => 'Developer tools',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel',
      'path' => 'Area',
    ),
    'TestDriver_Area_Devel_AppSettings' => 
    array (
      'id' => 'AppSettings',
      'urlName' => 'appsettings',
      'urlPath' => 'devel.appsettings',
      'title' => 'Application settings',
      'navigationTitle' => 'Application settings',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_AppSettings',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_Appinterface' => 
    array (
      'id' => 'Appinterface',
      'urlName' => 'appinterface',
      'urlPath' => 'devel.appinterface',
      'title' => 'Application interface reference',
      'navigationTitle' => 'Interface refs',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Appinterface',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_Errorlog' => 
    array (
      'id' => 'Errorlog',
      'urlName' => 'errorlog',
      'urlPath' => 'devel.errorlog',
      'title' => 'Error log',
      'navigationTitle' => 'Error log',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Errorlog',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_Errorlog_List' => 
    array (
      'id' => 'List',
      'urlName' => 'list',
      'urlPath' => 'devel.errorlog.list',
      'title' => 'Error log',
      'navigationTitle' => 'List',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Errorlog_List',
      'path' => 'Area/Devel/Errorlog',
    ),
    'TestDriver_Area_Devel_Errorlog_View' => 
    array (
      'id' => 'View',
      'urlName' => 'view',
      'urlPath' => 'devel.errorlog.view',
      'title' => 'View error log',
      'navigationTitle' => 'View',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Errorlog_View',
      'path' => 'Area/Devel/Errorlog',
    ),
    'TestDriver_Area_Devel_Maintenance' => 
    array (
      'id' => 'Maintenance',
      'urlName' => 'maintenance',
      'urlPath' => 'devel.maintenance',
      'title' => 'Planned maintenance',
      'navigationTitle' => 'Maintenance',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Maintenance',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_Maintenance_Create' => 
    array (
      'id' => 'Create',
      'urlName' => 'create',
      'urlPath' => 'devel.maintenance.create',
      'title' => 'Create maintenance plan',
      'navigationTitle' => 'Create plan',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Maintenance_Create',
      'path' => 'Area/Devel/Maintenance',
    ),
    'TestDriver_Area_Devel_Messagelog' => 
    array (
      'id' => 'Messagelog',
      'urlName' => 'messagelog',
      'urlPath' => 'devel.messagelog',
      'title' => 'Application messagelog',
      'navigationTitle' => 'Messagelog',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Messagelog',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_Overview' => 
    array (
      'id' => 'Overview',
      'urlName' => 'overview',
      'urlPath' => 'devel.overview',
      'title' => 'Developer tools overview',
      'navigationTitle' => 'Overview',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_Overview',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_RightsOverview' => 
    array (
      'id' => 'RightsOverview',
      'urlName' => 'rightsoverview',
      'urlPath' => 'devel.rightsoverview',
      'title' => 'User rights overview',
      'navigationTitle' => 'User rights',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver_Area_Devel_RightsOverview',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_WhatsNewEditor' => 
    array (
      'id' => 'WhatsNewEditor',
      'urlName' => 'whatsneweditor',
      'urlPath' => 'devel.whatsneweditor',
      'title' => 'What\'s new editor',
      'navigationTitle' => 'What\'s new editor',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_WhatsNewEditor',
      'path' => 'Area/Devel',
    ),
    'TestDriver_Area_Devel_WhatsNewEditor_Edit' => 
    array (
      'id' => 'Edit',
      'urlName' => 'edit',
      'urlPath' => 'devel.whatsneweditor.edit',
      'title' => 'Edit a version',
      'navigationTitle' => 'Edit',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel_WhatsNewEditor_Edit',
      'path' => 'Area/Devel/WhatsNewEditor',
    ),
    'TestDriver_Area_Settings' => 
    array (
      'id' => 'Settings',
      'urlName' => 'settings',
      'urlPath' => 'settings',
      'title' => 'User settings',
      'navigationTitle' => 'User settings',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Settings',
      'path' => 'Area',
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
      'id' => 'Devel',
      'urlName' => 'devel',
      'urlPath' => 'devel',
      'title' => 'Developer tools',
      'navigationTitle' => 'Developer tools',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'TestDriver_Area_Devel',
      'path' => 'Area',
      'subscreens' => 
      array (
        'appconfig' => 
        array (
          'id' => 'AppConfigScreen',
          'urlName' => 'appconfig',
          'urlPath' => 'devel.appconfig',
          'title' => 'Application configuration',
          'navigationTitle' => 'Configuration',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\Devel\\AppConfigScreen',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'appinterface' => 
        array (
          'id' => 'Appinterface',
          'urlName' => 'appinterface',
          'urlPath' => 'devel.appinterface',
          'title' => 'Application interface reference',
          'navigationTitle' => 'Interface refs',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_Appinterface',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'appsets' => 
        array (
          'id' => 'AppSetsScreen',
          'urlName' => 'appsets',
          'urlPath' => 'devel.appsets',
          'title' => 'Application interface sets',
          'navigationTitle' => 'Appsets',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
            'list' => 
            array (
              'id' => 'AppSetsListScreen',
              'urlName' => 'list',
              'urlPath' => 'devel.appsets.list',
              'title' => 'List of application sets',
              'navigationTitle' => 'List',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\AppSetsListScreen',
              'path' => 'Area/Devel/AppSetsScreen',
              'subscreens' => 
              array (
              ),
            ),
            'create' => 
            array (
              'id' => 'CreateAppSetScreen',
              'urlName' => 'create',
              'urlPath' => 'devel.appsets.create',
              'title' => 'Create a new application set',
              'navigationTitle' => 'Create new set',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\CreateAppSetScreen',
              'path' => 'Area/Devel/AppSetsScreen',
              'subscreens' => 
              array (
              ),
            ),
            'delete' => 
            array (
              'id' => 'DeleteAppSetScreen',
              'urlName' => 'delete',
              'urlPath' => 'devel.appsets.delete',
              'title' => 'Delete an application set',
              'navigationTitle' => 'Delete set',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\DeleteAppSetScreen',
              'path' => 'Area/Devel/AppSetsScreen',
              'subscreens' => 
              array (
              ),
            ),
            'edit' => 
            array (
              'id' => 'EditAppSetScreen',
              'urlName' => 'edit',
              'urlPath' => 'devel.appsets.edit',
              'title' => 'Create a new application set',
              'navigationTitle' => 'Create new set',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver\\Area\\Devel\\AppSetsScreen\\EditAppSetScreen',
              'path' => 'Area/Devel/AppSetsScreen',
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'appsettings' => 
        array (
          'id' => 'AppSettings',
          'urlName' => 'appsettings',
          'urlPath' => 'devel.appsettings',
          'title' => 'Application settings',
          'navigationTitle' => 'Application settings',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_AppSettings',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'cache-control' => 
        array (
          'id' => 'CacheControlSceen',
          'urlName' => 'cache-control',
          'urlPath' => 'devel.cache-control',
          'title' => 'Cache control',
          'navigationTitle' => 'Cache control',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\Devel\\CacheControlSceen',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'deploy-history' => 
        array (
          'id' => 'DeploymentHistoryScreen',
          'urlName' => 'deploy-history',
          'urlPath' => 'devel.deploy-history',
          'title' => 'Deployment history',
          'navigationTitle' => 'Deployment history',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\Devel\\DeploymentHistoryScreen',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'errorlog' => 
        array (
          'id' => 'Errorlog',
          'urlName' => 'errorlog',
          'urlPath' => 'devel.errorlog',
          'title' => 'Error log',
          'navigationTitle' => 'Error log',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_Errorlog',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
            'list' => 
            array (
              'id' => 'List',
              'urlName' => 'list',
              'urlPath' => 'devel.errorlog.list',
              'title' => 'Error log',
              'navigationTitle' => 'List',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver_Area_Devel_Errorlog_List',
              'path' => 'Area/Devel/Errorlog',
              'subscreens' => 
              array (
              ),
            ),
            'view' => 
            array (
              'id' => 'View',
              'urlName' => 'view',
              'urlPath' => 'devel.errorlog.view',
              'title' => 'View error log',
              'navigationTitle' => 'View',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver_Area_Devel_Errorlog_View',
              'path' => 'Area/Devel/Errorlog',
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'maintenance' => 
        array (
          'id' => 'Maintenance',
          'urlName' => 'maintenance',
          'urlPath' => 'devel.maintenance',
          'title' => 'Planned maintenance',
          'navigationTitle' => 'Maintenance',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_Maintenance',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
            'create' => 
            array (
              'id' => 'Create',
              'urlName' => 'create',
              'urlPath' => 'devel.maintenance.create',
              'title' => 'Create maintenance plan',
              'navigationTitle' => 'Create plan',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver_Area_Devel_Maintenance_Create',
              'path' => 'Area/Devel/Maintenance',
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'messagelog' => 
        array (
          'id' => 'Messagelog',
          'urlName' => 'messagelog',
          'urlPath' => 'devel.messagelog',
          'title' => 'Application messagelog',
          'navigationTitle' => 'Messagelog',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_Messagelog',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'overview' => 
        array (
          'id' => 'Overview',
          'urlName' => 'overview',
          'urlPath' => 'devel.overview',
          'title' => 'Developer tools overview',
          'navigationTitle' => 'Overview',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_Overview',
          'path' => 'Area/Devel',
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
          'class' => 'TestDriver\\Area\\Devel\\RenamerMode',
          'path' => 'Area/Devel',
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
              'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ConfigurationSubmode',
              'path' => 'Area/Devel/RenamerMode',
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
              'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ExportSubmode',
              'path' => 'Area/Devel/RenamerMode',
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
              'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ReplaceSubmode',
              'path' => 'Area/Devel/RenamerMode',
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
              'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\ResultsSubmode',
              'path' => 'Area/Devel/RenamerMode',
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
              'class' => 'TestDriver\\Area\\Devel\\RenamerMode\\SearchSubmode',
              'path' => 'Area/Devel/RenamerMode',
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'rightsoverview' => 
        array (
          'id' => 'RightsOverview',
          'urlName' => 'rightsoverview',
          'urlPath' => 'devel.rightsoverview',
          'title' => 'User rights overview',
          'navigationTitle' => 'User rights',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'TestDriver_Area_Devel_RightsOverview',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'users' => 
        array (
          'id' => 'UsersScreen',
          'urlName' => 'users',
          'urlPath' => 'devel.users',
          'title' => 'Users management',
          'navigationTitle' => 'Users',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver\\Area\\Devel\\UsersScreen',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
          ),
        ),
        'whatsneweditor' => 
        array (
          'id' => 'WhatsNewEditor',
          'urlName' => 'whatsneweditor',
          'urlPath' => 'devel.whatsneweditor',
          'title' => 'What\'s new editor',
          'navigationTitle' => 'What\'s new editor',
          'requiredRight' => NULL,
          'featureRights' => NULL,
          'class' => 'TestDriver_Area_Devel_WhatsNewEditor',
          'path' => 'Area/Devel',
          'subscreens' => 
          array (
            'edit' => 
            array (
              'id' => 'Edit',
              'urlName' => 'edit',
              'urlPath' => 'devel.whatsneweditor.edit',
              'title' => 'Edit a version',
              'navigationTitle' => 'Edit',
              'requiredRight' => NULL,
              'featureRights' => NULL,
              'class' => 'TestDriver_Area_Devel_WhatsNewEditor_Edit',
              'path' => 'Area/Devel/WhatsNewEditor',
              'subscreens' => 
              array (
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
      'class' => 'TestDriver_Area_Settings',
      'path' => 'Area',
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
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
  ),
);
