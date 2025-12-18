<?php

declare(strict_types=1);

return array (
  'urlPaths' => 
  array (
    'api-clients' => 'Application\\API\\Admin\\Screens\\APIClientsArea',
    'api-clients.create' => 'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode',
    'api-clients.list' => 'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode',
    'api-clients.view' => 'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode',
    'api-clients.view.api_keys' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode',
    'api-clients.view.api_keys.create' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction',
    'api-clients.view.api_keys.list' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction',
    'api-clients.view.api_keys.settings' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction',
    'api-clients.view.api_keys.status' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction',
    'api-clients.view.settings' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode',
    'api-clients.view.status' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode',
    'countries' => 'Application\\Countries\\Admin\\Screens\\CountriesArea',
    'countries.create' => 'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen',
    'countries.list' => 'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen',
    'countries.view' => 'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen',
    'countries.view.settings' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen',
    'countries.view.status' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen',
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
    'devel.deepl-test' => 'DeeplHelper\\Admin\\Screens\\DeepLTestScreen',
    'devel.deploy-history' => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
    'devel.errorlog' => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
    'devel.errorlog.list' => 'Application\\ErrorLog\\Admin\\Screens\\ListSubmode',
    'devel.errorlog.view' => 'Application\\ErrorLog\\Admin\\Screens\\ViewSubmode',
    'devel.maintenance' => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
    'devel.maintenance.create' => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
    'devel.maintenance.list' => 'Application\\Maintenance\\Admin\\Screens\\ListSubmode',
    'devel.messagelog' => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
    'devel.overview' => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
    'devel.renamer' => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
    'devel.renamer.configuration' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ConfigurationSubmode',
    'devel.renamer.export' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ExportSubmode',
    'devel.renamer.replace' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ReplaceSubmode',
    'devel.renamer.results' => 'Application\\Renamer\\Admin\\Screens\\Submode\\ResultsSubmode',
    'devel.renamer.search' => 'Application\\Renamer\\Admin\\Screens\\Submode\\SearchSubmode',
    'devel.rightsoverview' => 'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode',
    'devel.sitemap' => 'Application\\Admin\\Index\\Screens\\SitemapMode',
    'devel.translations' => 'Application\\Languages\\Admin\\Screens\\UITranslationDevMode',
    'devel.whatsneweditor' => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
    'devel.whatsneweditor.create' => 'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode',
    'devel.whatsneweditor.edit' => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
    'devel.whatsneweditor.list' => 'Application\\WhatsNew\\Admin\\Screens\\ListSubmode',
    'media' => 'Application\\Media\\Admin\\Screens\\MediaLibraryArea',
    'media.create' => 'Application\\Media\\Admin\\Screens\\Mode\\CreateMode',
    'media.image-gallery' => 'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode',
    'media.list' => 'Application\\Media\\Admin\\Screens\\Mode\\ListMode',
    'media.settings' => 'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode',
    'media.view' => 'Application\\Media\\Admin\\Screens\\Mode\\ViewMode',
    'media.view.settings' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
    'media.view.status' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode',
    'media.view.tagging' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode',
    'news' => 'Application\\NewsCentral\\Admin\\Screens\\ManageNewsArea',
    'news.categories-list' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode',
    'news.create-alert' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen',
    'news.create-article' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen',
    'news.create-category' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode',
    'news.list' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode',
    'news.view' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode',
    'news.view-category' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode',
    'news.view-category.settings' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode',
    'news.view.settings' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode',
    'news.view.status' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode',
    'quicknav' => 'TestDriver\\Area\\QuickNavScreen',
    'read-news' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNewsArea',
    'read-news.article' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen',
    'read-news.list' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode',
    'revisionable' => 'TestDriver\\Area\\RevisionableScreen',
    'revisionable.list' => 'TestDriver\\Area\\RevisionableScreen\\RevisionableListScreen',
    'settings' => 'Application\\Users\\Admin\\Screens\\UserSettingsArea',
    'tags' => 'Application\\Tags\\Admin\\Screens\\Area\\TagsArea',
    'tags.create' => 'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode',
    'tags.list' => 'Application\\Tags\\Admin\\Screens\\Mode\\ListMode',
    'tags.view-tag' => 'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode',
    'tags.view-tag.tag-settings' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
    'tags.view-tag.tag-tree' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode',
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
    'time-tracker' => 'Application\\TimeTracker\\Admin\\Screens\\TimeTrackerArea',
    'time-tracker.auto-fill' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode',
    'time-tracker.create' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode',
    'time-tracker.create-time-span' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode',
    'time-tracker.export' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode',
    'time-tracker.import' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode',
    'time-tracker.list' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode',
    'time-tracker.list.day' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode',
    'time-tracker.list.global' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode',
    'time-tracker.list.time-settings' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode',
    'time-tracker.list.time-spans-list' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode',
    'time-tracker.view' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode',
    'time-tracker.view.settings' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode',
    'time-tracker.view.status' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode',
    'users' => 'Application\\Users\\Admin\\Screens\\Manage\\ManageUsersArea',
    'users.create' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode',
    'users.list' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode',
    'users.view' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode',
    'users.view.settings' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode',
    'users.view.status' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode',
    'welcome' => 'Application\\Admin\\Welcome\\Screens\\WelcomeArea',
    'welcome.overview' => 'Application\\Admin\\Welcome\\Screens\\OverviewMode',
    'welcome.settings' => 'Application\\Admin\\Welcome\\Screens\\SettingsMode',
    'wizardtest' => 'TestDriver_Area_WizardTest',
    'wizardtest.wizard' => 'TestDriver_Area_WizardTest_Wizard',
  ),
  'flat' => 
  array (
    'Application\\API\\Admin\\Screens\\APIClientsArea' => 
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
      'class' => 'Application\\API\\Admin\\Screens\\APIClientsArea',
      'path' => 'framework-classes:Application/API/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode',
        1 => 'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode',
        2 => 'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode',
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode' => 
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
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode' => 
    array (
      'id' => 'CreateClientMode',
      'urlName' => 'create',
      'urlPath' => 'api-clients.create',
      'title' => 'Create a new API Client',
      'navigationTitle' => 'Create new client',
      'requiredRight' => 'CreateAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode' => 
    array (
      'id' => 'ViewClientMode',
      'urlName' => 'view',
      'urlPath' => 'api-clients.view',
      'title' => 'View API Client',
      'navigationTitle' => 'View Client',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode',
        1 => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode',
        2 => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode',
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode' => 
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
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
        0 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction',
        1 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction',
        2 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction',
        3 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction',
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction' => 
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
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction' => 
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
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction' => 
    array (
      'id' => 'APIKeysListAction',
      'urlName' => 'list',
      'urlPath' => 'api-clients.view.api_keys.list',
      'title' => 'Overview of API Keys',
      'navigationTitle' => 'Overview',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction' => 
    array (
      'id' => 'CreateAPIKeyAction',
      'urlName' => 'create',
      'urlPath' => 'api-clients.view.api_keys.create',
      'title' => 'Create an API Key',
      'navigationTitle' => 'Create new key',
      'requiredRight' => NULL,
      'featureRights' => NULL,
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode' => 
    array (
      'id' => 'ClientSettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'api-clients.view.settings',
      'title' => 'API Client Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode' => 
    array (
      'id' => 'ClientStatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'api-clients.view.status',
      'title' => 'API Client Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewAPIClients',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode',
      'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Index\\Screens\\SitemapMode' => 
    array (
      'id' => 'SitemapMode',
      'urlName' => 'sitemap',
      'urlPath' => 'devel.sitemap',
      'title' => 'Application Sitemap',
      'navigationTitle' => 'Sitemap',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Index\\Screens\\SitemapMode',
      'path' => 'framework-classes:Application/Admin/Index/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Welcome\\Screens\\OverviewMode' => 
    array (
      'id' => 'OverviewMode',
      'urlName' => 'overview',
      'urlPath' => 'welcome.overview',
      'title' => 'Quickstart',
      'navigationTitle' => 'Quickstart',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Welcome\\Screens\\OverviewMode',
      'path' => 'framework-classes:Application/Admin/Welcome/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Welcome\\Screens\\SettingsMode' => 
    array (
      'id' => 'SettingsMode',
      'urlName' => 'settings',
      'urlPath' => 'welcome.settings',
      'title' => 'Quickstart settings',
      'navigationTitle' => '',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Welcome\\Screens\\SettingsMode',
      'path' => 'framework-classes:Application/Admin/Welcome/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Admin\\Welcome\\Screens\\WelcomeArea' => 
    array (
      'id' => 'WelcomeArea',
      'urlName' => 'welcome',
      'urlPath' => 'welcome',
      'title' => 'Quickstart',
      'navigationTitle' => '',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Welcome\\Screens\\WelcomeArea',
      'path' => 'framework-classes:Application/Admin/Welcome/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Admin\\Welcome\\Screens\\OverviewMode',
        1 => 'Application\\Admin\\Welcome\\Screens\\SettingsMode',
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
      'path' => 'framework-classes:Application/AppSettings/Admin/Screens',
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
      'path' => 'framework-classes:Application/CacheControl/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\CountriesArea' => 
    array (
      'id' => 'CountriesArea',
      'urlName' => 'countries',
      'urlPath' => 'countries',
      'title' => 'Countries',
      'navigationTitle' => 'Countries',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Countries\\Admin\\Screens\\CountriesArea',
      'path' => 'framework-classes:Application/Countries/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen',
        1 => 'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen',
        2 => 'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen',
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen' => 
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
      'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen',
      'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen' => 
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
      'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen',
      'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen' => 
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
      'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen',
      'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen',
        1 => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen',
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen' => 
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
      'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen',
      'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen' => 
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
      'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen',
      'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode/View',
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
      'path' => 'framework-classes:Application/DeploymentRegistry/Admin/Screens',
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
      'path' => 'framework-classes:Application/Development/Admin/Screens',
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
      'path' => 'framework-classes:Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Admin\\Index\\Screens\\SitemapMode',
        1 => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
        2 => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
        3 => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
        4 => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
        5 => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
        6 => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
        7 => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
        8 => 'Application\\Languages\\Admin\\Screens\\UITranslationDevMode',
        9 => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
        10 => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
        11 => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
        12 => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
        13 => 'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode',
        14 => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
        15 => 'DeeplHelper\\Admin\\Screens\\DeepLTestScreen',
        16 => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
        17 => 'UI\\Admin\\Screens\\CSSGenDevelMode',
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
      'path' => 'framework-classes:Application/Development/Admin/Screens',
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
      'path' => 'framework-classes:Application/Environments/Admin/Screens',
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
      'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
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
      'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
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
      'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Languages\\Admin\\Screens\\UITranslationDevMode' => 
    array (
      'id' => 'UITranslationDevMode',
      'urlName' => 'translations',
      'urlPath' => 'devel.translations',
      'title' => 'UI Translation tools',
      'navigationTitle' => 'Translation',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Languages\\Admin\\Screens\\UITranslationDevMode',
      'path' => 'framework-classes:Application/Languages/Admin/Screens',
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
      'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Maintenance\\Admin\\Screens\\ListSubmode' => 
    array (
      'id' => 'ListSubmode',
      'urlName' => 'list',
      'urlPath' => 'devel.maintenance.list',
      'title' => 'Maintenance plans',
      'navigationTitle' => 'Maintenance plans',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Maintenance\\Admin\\Screens\\ListSubmode',
      'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
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
      'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
        1 => 'Application\\Maintenance\\Admin\\Screens\\ListSubmode',
      ),
    ),
    'Application\\Media\\Admin\\Screens\\MediaLibraryArea' => 
    array (
      'id' => 'MediaLibraryArea',
      'urlName' => 'media',
      'urlPath' => 'media',
      'title' => 'Media library',
      'navigationTitle' => 'Media',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\MediaLibraryArea',
      'path' => 'framework-classes:Application/Media/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Media\\Admin\\Screens\\Mode\\CreateMode',
        1 => 'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode',
        2 => 'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode',
        3 => 'Application\\Media\\Admin\\Screens\\Mode\\ListMode',
        4 => 'Application\\Media\\Admin\\Screens\\Mode\\ViewMode',
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\CreateMode' => 
    array (
      'id' => 'CreateMode',
      'urlName' => 'create',
      'urlPath' => 'media.create',
      'title' => 'Add a media file',
      'navigationTitle' => 'Add media',
      'requiredRight' => 'CreateMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\CreateMode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode' => 
    array (
      'id' => 'GlobalSettingsMode',
      'urlName' => 'settings',
      'urlPath' => 'media.settings',
      'title' => 'Media settings',
      'navigationTitle' => 'Media settings',
      'requiredRight' => 'AdminMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode' => 
    array (
      'id' => 'ImageGalleryMode',
      'urlName' => 'image-gallery',
      'urlPath' => 'media.image-gallery',
      'title' => 'Image gallery',
      'navigationTitle' => 'Image gallery',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\ListMode' => 
    array (
      'id' => 'ListMode',
      'urlName' => 'list',
      'urlPath' => 'media.list',
      'title' => 'Available media files',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ListMode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\ViewMode' => 
    array (
      'id' => 'ViewMode',
      'urlName' => 'view',
      'urlPath' => 'media.view',
      'title' => 'Media file',
      'navigationTitle' => 'Media file',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ViewMode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
        1 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode',
        2 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode',
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode' => 
    array (
      'id' => 'SettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'media.view.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
        'Edit the settings' => 'EditMedia',
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode' => 
    array (
      'id' => 'StatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'media.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode' => 
    array (
      'id' => 'TagsSubmode',
      'urlName' => 'tagging',
      'urlPath' => 'media.view.tagging',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => 'EditMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode',
      'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
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
      'path' => 'framework-classes:Application/Messagelogs/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\ManageNewsArea' => 
    array (
      'id' => 'ManageNewsArea',
      'urlName' => 'news',
      'urlPath' => 'news',
      'title' => 'Application news central',
      'navigationTitle' => 'News central',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ManageNewsArea',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode',
        1 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen',
        2 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen',
        3 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode',
        4 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode',
        5 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode',
        6 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode',
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode' => 
    array (
      'id' => 'CategoriesListMode',
      'urlName' => 'categories-list',
      'urlPath' => 'news.categories-list',
      'title' => 'Available categories',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen' => 
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
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen' => 
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
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode' => 
    array (
      'id' => 'CreateCategoryMode',
      'urlName' => 'create-category',
      'urlPath' => 'news.create-category',
      'title' => 'Create a news category',
      'navigationTitle' => 'Create Category',
      'requiredRight' => 'EditNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode' => 
    array (
      'id' => 'NewsListMode',
      'urlName' => 'list',
      'urlPath' => 'news.list',
      'title' => 'Available news articles',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode' => 
    array (
      'id' => 'ViewArticleMode',
      'urlName' => 'view',
      'urlPath' => 'news.view',
      'title' => 'View news entry',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode',
        1 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode',
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode' => 
    array (
      'id' => 'ArticleSettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'news.view.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
        'Modify the settings' => 'EditNews',
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewArticle',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode' => 
    array (
      'id' => 'ArticleStatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'news.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewArticle',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode' => 
    array (
      'id' => 'ViewCategoryMode',
      'urlName' => 'view-category',
      'urlPath' => 'news.view-category',
      'title' => 'View news category',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode',
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode' => 
    array (
      'id' => 'CategorySettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'news.view-category.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewNews',
      'featureRights' => 
      array (
        'Modify the settings' => 'EditNews',
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewCategory',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\ReadNewsArea' => 
    array (
      'id' => 'ReadNewsArea',
      'urlName' => 'read-news',
      'urlPath' => 'read-news',
      'title' => 'AppTestSuite news',
      'navigationTitle' => 'News',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNewsArea',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode',
        1 => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen',
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode' => 
    array (
      'id' => 'ArticlesListMode',
      'urlName' => 'list',
      'urlPath' => 'read-news.list',
      'title' => 'AppTestSuite news',
      'navigationTitle' => 'News',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ReadNews',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen' => 
    array (
      'id' => 'ReadArticleScreen',
      'urlName' => 'article',
      'urlPath' => 'read-news.article',
      'title' => 'News Article',
      'navigationTitle' => 'Article',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ReadNews',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Mode',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
      'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
      'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
      'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
      'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
      'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
      'path' => 'framework-classes:Application/Sets/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Area\\TagsArea' => 
    array (
      'id' => 'TagsArea',
      'urlName' => 'tags',
      'urlPath' => 'tags',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Area\\TagsArea',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Area',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode',
        1 => 'Application\\Tags\\Admin\\Screens\\Mode\\ListMode',
        2 => 'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode',
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode' => 
    array (
      'id' => 'CreateMode',
      'urlName' => 'create',
      'urlPath' => 'tags.create',
      'title' => 'Create a tag',
      'navigationTitle' => 'Create tag',
      'requiredRight' => 'CreateTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Mode\\ListMode' => 
    array (
      'id' => 'ListMode',
      'urlName' => 'list',
      'urlPath' => 'tags.list',
      'title' => 'Available root tags',
      'navigationTitle' => 'List',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
        'Delete tags' => 'DeleteTags',
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\ListMode',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode' => 
    array (
      'id' => 'ViewMode',
      'urlName' => 'view-tag',
      'urlPath' => 'tags.view-tag',
      'title' => 'View a tag',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
        1 => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode',
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode' => 
    array (
      'id' => 'SettingsSubmode',
      'urlName' => 'tag-settings',
      'urlPath' => 'tags.view-tag.tag-settings',
      'title' => 'Edit tag settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
        'Edit the settings' => 'EditTags',
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode' => 
    array (
      'id' => 'TagTreeSubmode',
      'urlName' => 'tag-tree',
      'urlPath' => 'tags.view-tag.tag-tree',
      'title' => 'Tag tree',
      'navigationTitle' => 'Tree',
      'requiredRight' => 'EditTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode' => 
    array (
      'id' => 'AutoFillMode',
      'urlName' => 'auto-fill',
      'urlPath' => 'time-tracker.auto-fill',
      'title' => 'Auto-fill time entries',
      'navigationTitle' => 'Auto-fill',
      'requiredRight' => 'ViewTimeFilters',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode' => 
    array (
      'id' => 'CreateEntryMode',
      'urlName' => 'create',
      'urlPath' => 'time-tracker.create',
      'title' => 'Create a time entry',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTimeFilters',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode' => 
    array (
      'id' => 'CreateTimeSpanMode',
      'urlName' => 'create-time-span',
      'urlPath' => 'time-tracker.create-time-span',
      'title' => 'Create a time span',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTimeFilters',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode' => 
    array (
      'id' => 'ExportMode',
      'urlName' => 'export',
      'urlPath' => 'time-tracker.export',
      'title' => 'Export time entries',
      'navigationTitle' => 'Export',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode' => 
    array (
      'id' => 'ImportMode',
      'urlName' => 'import',
      'urlPath' => 'time-tracker.import',
      'title' => 'Import time entries',
      'navigationTitle' => 'Import',
      'requiredRight' => 'EditTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode' => 
    array (
      'id' => 'ListMode',
      'urlName' => 'list',
      'urlPath' => 'time-tracker.list',
      'title' => 'Available time entries',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode',
        1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode',
        2 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode',
        3 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode',
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode' => 
    array (
      'id' => 'DayListSubmode',
      'urlName' => 'day',
      'urlPath' => 'time-tracker.list.day',
      'title' => 'Day view',
      'navigationTitle' => 'Day view',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode' => 
    array (
      'id' => 'GlobalListSubmode',
      'urlName' => 'global',
      'urlPath' => 'time-tracker.list.global',
      'title' => 'Available time entries',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode' => 
    array (
      'id' => 'GlobalSettingsSubmode',
      'urlName' => 'time-settings',
      'urlPath' => 'time-tracker.list.time-settings',
      'title' => 'Global Settings',
      'navigationTitle' => 'Global Settings',
      'requiredRight' => 'EditTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode' => 
    array (
      'id' => 'TimeSpanListSubmode',
      'urlName' => 'time-spans-list',
      'urlPath' => 'time-tracker.list.time-spans-list',
      'title' => 'Time Spans',
      'navigationTitle' => 'Time Spans',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode' => 
    array (
      'id' => 'ViewMode',
      'urlName' => 'view',
      'urlPath' => 'time-tracker.view',
      'title' => 'View a time entry',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode',
        1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode',
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode' => 
    array (
      'id' => 'SettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'time-tracker.view.settings',
      'title' => 'Settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
        'Edit settings' => 'EditTimeEntries',
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode' => 
    array (
      'id' => 'StatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'time-tracker.view.status',
      'title' => 'Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\TimeTracker\\Admin\\Screens\\TimeTrackerArea' => 
    array (
      'id' => 'TimeTrackerArea',
      'urlName' => 'time-tracker',
      'urlPath' => 'time-tracker',
      'title' => 'Time Tracker',
      'navigationTitle' => 'Time Tracker',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\TimeTrackerArea',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode',
        1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode',
        2 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode',
        3 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode',
        4 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode',
        5 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode',
        6 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode',
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\ManageUsersArea' => 
    array (
      'id' => 'ManageUsersArea',
      'urlName' => 'users',
      'urlPath' => 'users',
      'title' => 'Users',
      'navigationTitle' => 'Users',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\ManageUsersArea',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode',
        1 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode',
        2 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode',
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode' => 
    array (
      'id' => 'CreateMode',
      'urlName' => 'create',
      'urlPath' => 'users.create',
      'title' => 'Create a new user',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'CreateUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode' => 
    array (
      'id' => 'ListMode',
      'urlName' => 'list',
      'urlPath' => 'users.list',
      'title' => 'Available users',
      'navigationTitle' => 'Overview',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode' => 
    array (
      'id' => 'ViewMode',
      'urlName' => 'view',
      'urlPath' => 'users.view',
      'title' => 'View user details',
      'navigationTitle' => 'View',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode',
        1 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode',
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode' => 
    array (
      'id' => 'SettingsSubmode',
      'urlName' => 'settings',
      'urlPath' => 'users.view.settings',
      'title' => 'User settings',
      'navigationTitle' => 'Settings',
      'requiredRight' => 'EditUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode' => 
    array (
      'id' => 'StatusSubmode',
      'urlName' => 'status',
      'urlPath' => 'users.view.status',
      'title' => 'User Status',
      'navigationTitle' => 'Status',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode/View',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode' => 
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
      'class' => 'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode',
      'path' => 'framework-classes:Application/Users/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\Users\\Admin\\Screens\\UserSettingsArea' => 
    array (
      'id' => 'UserSettingsArea',
      'urlName' => 'settings',
      'urlPath' => 'settings',
      'title' => 'User settings',
      'navigationTitle' => 'User settings',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\UserSettingsArea',
      'path' => 'framework-classes:Application/Users/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode' => 
    array (
      'id' => 'CreateSubmode',
      'urlName' => 'create',
      'urlPath' => 'devel.whatsneweditor.create',
      'title' => 'Create a new version',
      'navigationTitle' => 'Create',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode',
      'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
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
      'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
    ),
    'Application\\WhatsNew\\Admin\\Screens\\ListSubmode' => 
    array (
      'id' => 'ListSubmode',
      'urlName' => 'list',
      'urlPath' => 'devel.whatsneweditor.list',
      'title' => 'Available versions',
      'navigationTitle' => 'Available versions',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\WhatsNew\\Admin\\Screens\\ListSubmode',
      'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
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
      'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode',
        1 => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
        2 => 'Application\\WhatsNew\\Admin\\Screens\\ListSubmode',
      ),
    ),
    'DeeplHelper\\Admin\\Screens\\DeepLTestScreen' => 
    array (
      'id' => 'DeepLTestScreen',
      'urlName' => 'deepl-test',
      'urlPath' => 'devel.deepl-test',
      'title' => 'DeepL Test',
      'navigationTitle' => 'DeepL Test',
      'requiredRight' => 'Developer',
      'featureRights' => 
      array (
      ),
      'class' => 'DeeplHelper\\Admin\\Screens\\DeepLTestScreen',
      'path' => 'framework-classes:DeeplHelper/Admin/Screens',
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
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\QuickNavScreen',
      'path' => 'driver-classes:Area',
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
      'path' => 'driver-classes:Area',
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
      'path' => 'driver-classes:Area/RevisionableScreen',
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
      'path' => 'driver-classes:Area',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area/TestingScreen',
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
      'path' => 'driver-classes:Area',
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
      'path' => 'driver-classes:Area/WizardTest',
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
      'path' => 'framework-classes:UI/Admin/Screens',
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
      'path' => 'framework-classes:UI/Admin/Screens',
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
      'class' => 'Application\\API\\Admin\\Screens\\APIClientsArea',
      'path' => 'framework-classes:Application/API/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode',
        1 => 'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode',
        2 => 'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode',
      ),
      'subscreens' => 
      array (
        'view' => 
        array (
          'id' => 'ViewClientMode',
          'urlName' => 'view',
          'urlPath' => 'api-clients.view',
          'title' => 'View API Client',
          'navigationTitle' => 'View Client',
          'requiredRight' => 'ViewAPIClients',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\API\\Admin\\Screens\\Mode\\ViewClientMode',
          'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode',
            1 => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode',
            2 => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode',
          ),
          'subscreens' => 
          array (
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
              'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeysSubmode',
              'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
                0 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction',
                1 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction',
                2 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction',
                3 => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction',
              ),
              'subscreens' => 
              array (
                'create' => 
                array (
                  'id' => 'CreateAPIKeyAction',
                  'urlName' => 'create',
                  'urlPath' => 'api-clients.view.api_keys.create',
                  'title' => 'Create an API Key',
                  'navigationTitle' => 'Create new key',
                  'requiredRight' => NULL,
                  'featureRights' => NULL,
                  'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\CreateAPIKeyAction',
                  'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
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
                  'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeysListAction',
                  'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
                  'subscreenClasses' => 
                  array (
                  ),
                  'subscreens' => 
                  array (
                  ),
                ),
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
                  'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeySettingsAction',
                  'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
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
                  'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\APIKeys\\APIKeyStatusAction',
                  'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View/APIKeys',
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
              'id' => 'ClientSettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'api-clients.view.settings',
              'title' => 'API Client Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewAPIClients',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientSettingsSubmode',
              'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'ClientStatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'api-clients.view.status',
              'title' => 'API Client Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewAPIClients',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\API\\Admin\\Screens\\Mode\\View\\ClientStatusSubmode',
              'path' => 'framework-classes:Application/API/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
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
          'class' => 'Application\\API\\Admin\\Screens\\Mode\\ClientsListMode',
          'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'create' => 
        array (
          'id' => 'CreateClientMode',
          'urlName' => 'create',
          'urlPath' => 'api-clients.create',
          'title' => 'Create a new API Client',
          'navigationTitle' => 'Create new client',
          'requiredRight' => 'CreateAPIClients',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\API\\Admin\\Screens\\Mode\\CreateClientMode',
          'path' => 'framework-classes:Application/API/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
      'path' => 'driver-classes:Area',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
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
          'path' => 'driver-classes:Area/TestingScreen',
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
          'path' => 'driver-classes:Area/TestingScreen',
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
      'id' => 'UserSettingsArea',
      'urlName' => 'settings',
      'urlPath' => 'settings',
      'title' => 'User settings',
      'navigationTitle' => 'User settings',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\UserSettingsArea',
      'path' => 'framework-classes:Application/Users/Admin/Screens',
      'subscreenClasses' => 
      array (
      ),
      'subscreens' => 
      array (
      ),
    ),
    'users' => 
    array (
      'id' => 'ManageUsersArea',
      'urlName' => 'users',
      'urlPath' => 'users',
      'title' => 'Users',
      'navigationTitle' => 'Users',
      'requiredRight' => 'ViewUsers',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Users\\Admin\\Screens\\Manage\\ManageUsersArea',
      'path' => 'framework-classes:Application/Users/Admin/Screens/Manage',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode',
        1 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode',
        2 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode',
      ),
      'subscreens' => 
      array (
        'view' => 
        array (
          'id' => 'ViewMode',
          'urlName' => 'view',
          'urlPath' => 'users.view',
          'title' => 'View user details',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ViewMode',
          'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode',
            1 => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'SettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'users.view.settings',
              'title' => 'User settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'EditUsers',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\SettingsSubmode',
              'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'StatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'users.view.status',
              'title' => 'User Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewUsers',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\View\\StatusSubmode',
              'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'create' => 
        array (
          'id' => 'CreateMode',
          'urlName' => 'create',
          'urlPath' => 'users.create',
          'title' => 'Create a new user',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'CreateUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\CreateMode',
          'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'ListMode',
          'urlName' => 'list',
          'urlPath' => 'users.list',
          'title' => 'Available users',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewUsers',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Users\\Admin\\Screens\\Manage\\Mode\\ListMode',
          'path' => 'framework-classes:Application/Users/Admin/Screens/Manage/Mode',
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
      'path' => 'driver-classes:Area',
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
          'path' => 'driver-classes:Area/WizardTest',
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
      'id' => 'TimeTrackerArea',
      'urlName' => 'time-tracker',
      'urlPath' => 'time-tracker',
      'title' => 'Time Tracker',
      'navigationTitle' => 'Time Tracker',
      'requiredRight' => 'ViewTimeEntries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\TimeTracker\\Admin\\Screens\\TimeTrackerArea',
      'path' => 'framework-classes:Application/TimeTracker/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode',
        1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode',
        2 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode',
        3 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode',
        4 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode',
        5 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode',
        6 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode',
      ),
      'subscreens' => 
      array (
        'create' => 
        array (
          'id' => 'CreateEntryMode',
          'urlName' => 'create',
          'urlPath' => 'time-tracker.create',
          'title' => 'Create a time entry',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'ViewTimeFilters',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateEntryMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'import' => 
        array (
          'id' => 'ImportMode',
          'urlName' => 'import',
          'urlPath' => 'time-tracker.import',
          'title' => 'Import time entries',
          'navigationTitle' => 'Import',
          'requiredRight' => 'EditTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ImportMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'ListMode',
          'urlName' => 'list',
          'urlPath' => 'time-tracker.list',
          'title' => 'Available time entries',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode',
            1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode',
            2 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode',
            3 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode',
          ),
          'subscreens' => 
          array (
            'day' => 
            array (
              'id' => 'DayListSubmode',
              'urlName' => 'day',
              'urlPath' => 'time-tracker.list.day',
              'title' => 'Day view',
              'navigationTitle' => 'Day view',
              'requiredRight' => 'ViewTimeEntries',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\DayListSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'time-settings' => 
            array (
              'id' => 'GlobalSettingsSubmode',
              'urlName' => 'time-settings',
              'urlPath' => 'time-tracker.list.time-settings',
              'title' => 'Global Settings',
              'navigationTitle' => 'Global Settings',
              'requiredRight' => 'EditTimeEntries',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalSettingsSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'time-spans-list' => 
            array (
              'id' => 'TimeSpanListSubmode',
              'urlName' => 'time-spans-list',
              'urlPath' => 'time-tracker.list.time-spans-list',
              'title' => 'Time Spans',
              'navigationTitle' => 'Time Spans',
              'requiredRight' => 'ViewTimeEntries',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\TimeSpanListSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'global' => 
            array (
              'id' => 'GlobalListSubmode',
              'urlName' => 'global',
              'urlPath' => 'time-tracker.list.global',
              'title' => 'Available time entries',
              'navigationTitle' => 'Overview',
              'requiredRight' => 'ViewTimeEntries',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ListScreen\\GlobalListSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/List',
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
          'id' => 'ViewMode',
          'urlName' => 'view',
          'urlPath' => 'time-tracker.view',
          'title' => 'View a time entry',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode',
            1 => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode',
          ),
          'subscreens' => 
          array (
            'status' => 
            array (
              'id' => 'StatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'time-tracker.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewTimeEntries',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\StatusSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'settings' => 
            array (
              'id' => 'SettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'time-tracker.view.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewTimeEntries',
              'featureRights' => 
              array (
                'Edit settings' => 'EditTimeEntries',
              ),
              'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ViewScreen\\SettingsSubmode',
              'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'create-time-span' => 
        array (
          'id' => 'CreateTimeSpanMode',
          'urlName' => 'create-time-span',
          'urlPath' => 'time-tracker.create-time-span',
          'title' => 'Create a time span',
          'navigationTitle' => 'Settings',
          'requiredRight' => 'ViewTimeFilters',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\CreateTimeSpanMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'auto-fill' => 
        array (
          'id' => 'AutoFillMode',
          'urlName' => 'auto-fill',
          'urlPath' => 'time-tracker.auto-fill',
          'title' => 'Auto-fill time entries',
          'navigationTitle' => 'Auto-fill',
          'requiredRight' => 'ViewTimeFilters',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\AutoFillMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'export' => 
        array (
          'id' => 'ExportMode',
          'urlName' => 'export',
          'urlPath' => 'time-tracker.export',
          'title' => 'Export time entries',
          'navigationTitle' => 'Export',
          'requiredRight' => 'ViewTimeEntries',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\TimeTracker\\Admin\\Screens\\Mode\\ExportMode',
          'path' => 'framework-classes:Application/TimeTracker/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
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
      'path' => 'driver-classes:Area',
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
          'path' => 'driver-classes:Area/RevisionableScreen',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
      'path' => 'framework-classes:Application/Development/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Admin\\Index\\Screens\\SitemapMode',
        1 => 'Application\\AppSettings\\Admin\\Screens\\AppSettingsDevelMode',
        2 => 'Application\\CacheControl\\Admin\\Screens\\CacheControlMode',
        3 => 'Application\\DeploymentRegistry\\Admin\\Screens\\DeploymentHistoryMode',
        4 => 'Application\\Development\\Admin\\Screens\\DatabaseDumpDevMode',
        5 => 'Application\\Development\\Admin\\Screens\\DevelOverviewMode',
        6 => 'Application\\Environments\\Admin\\Screens\\AppConfigMode',
        7 => 'Application\\ErrorLog\\Admin\\Screens\\ErrorLogMode',
        8 => 'Application\\Languages\\Admin\\Screens\\UITranslationDevMode',
        9 => 'Application\\Maintenance\\Admin\\Screens\\MaintenanceMode',
        10 => 'Application\\Messagelogs\\Admin\\Screens\\MessageLogDevelMode',
        11 => 'Application\\Renamer\\Admin\\Screens\\Mode\\RenamerMode',
        12 => 'Application\\Sets\\Admin\\Screens\\ApplicationSetsMode',
        13 => 'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode',
        14 => 'Application\\WhatsNew\\Admin\\Screens\\WhatsNewEditorMode',
        15 => 'DeeplHelper\\Admin\\Screens\\DeepLTestScreen',
        16 => 'UI\\Admin\\Screens\\AppInterfaceDevelMode',
        17 => 'UI\\Admin\\Screens\\CSSGenDevelMode',
      ),
      'subscreens' => 
      array (
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
          'path' => 'framework-classes:Application/CacheControl/Admin/Screens',
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
          'path' => 'framework-classes:UI/Admin/Screens',
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
          'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
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
              'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
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
              'path' => 'framework-classes:Application/ErrorLog/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
          'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode',
            1 => 'Application\\WhatsNew\\Admin\\Screens\\EditSubmode',
            2 => 'Application\\WhatsNew\\Admin\\Screens\\ListSubmode',
          ),
          'subscreens' => 
          array (
            'list' => 
            array (
              'id' => 'ListSubmode',
              'urlName' => 'list',
              'urlPath' => 'devel.whatsneweditor.list',
              'title' => 'Available versions',
              'navigationTitle' => 'Available versions',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\WhatsNew\\Admin\\Screens\\ListSubmode',
              'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'create' => 
            array (
              'id' => 'CreateSubmode',
              'urlName' => 'create',
              'urlPath' => 'devel.whatsneweditor.create',
              'title' => 'Create a new version',
              'navigationTitle' => 'Create',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\WhatsNew\\Admin\\Screens\\CreateSubmode',
              'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
              'path' => 'framework-classes:Application/WhatsNew/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
          'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
              'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
              'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
              'path' => 'framework-classes:Application/Sets/Admin/Screens',
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
              'path' => 'framework-classes:Application/Sets/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
          'path' => 'framework-classes:UI/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
          'class' => 'Application\\Users\\Admin\\Screens\\RightsOverviewDevelMode',
          'path' => 'framework-classes:Application/Users/Admin/Screens',
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
          'path' => 'framework-classes:Application/Development/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
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
          'path' => 'framework-classes:Application/Environments/Admin/Screens',
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
          'path' => 'framework-classes:Application/Development/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
          'path' => 'framework-classes:Application/Messagelogs/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'deepl-test' => 
        array (
          'id' => 'DeepLTestScreen',
          'urlName' => 'deepl-test',
          'urlPath' => 'devel.deepl-test',
          'title' => 'DeepL Test',
          'navigationTitle' => 'DeepL Test',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'DeeplHelper\\Admin\\Screens\\DeepLTestScreen',
          'path' => 'framework-classes:DeeplHelper/Admin/Screens',
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
          'path' => 'framework-classes:Application/Renamer/Admin/Screens/Mode',
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
              'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
              'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
              'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
              'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
              'path' => 'framework-classes:Application/Renamer/Admin/Screens/Submode',
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
          'id' => 'UITranslationDevMode',
          'urlName' => 'translations',
          'urlPath' => 'devel.translations',
          'title' => 'UI Translation tools',
          'navigationTitle' => 'Translation',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Languages\\Admin\\Screens\\UITranslationDevMode',
          'path' => 'framework-classes:Application/Languages/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
          'path' => 'framework-classes:Application/AppSettings/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'sitemap' => 
        array (
          'id' => 'SitemapMode',
          'urlName' => 'sitemap',
          'urlPath' => 'devel.sitemap',
          'title' => 'Application Sitemap',
          'navigationTitle' => 'Sitemap',
          'requiredRight' => 'Developer',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Admin\\Index\\Screens\\SitemapMode',
          'path' => 'framework-classes:Application/Admin/Index/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
          'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Maintenance\\Admin\\Screens\\CreateSubmode',
            1 => 'Application\\Maintenance\\Admin\\Screens\\ListSubmode',
          ),
          'subscreens' => 
          array (
            'list' => 
            array (
              'id' => 'ListSubmode',
              'urlName' => 'list',
              'urlPath' => 'devel.maintenance.list',
              'title' => 'Maintenance plans',
              'navigationTitle' => 'Maintenance plans',
              'requiredRight' => 'Developer',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Maintenance\\Admin\\Screens\\ListSubmode',
              'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
              'path' => 'framework-classes:Application/Maintenance/Admin/Screens',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
          'path' => 'framework-classes:Application/DeploymentRegistry/Admin/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'countries' => 
    array (
      'id' => 'CountriesArea',
      'urlName' => 'countries',
      'urlPath' => 'countries',
      'title' => 'Countries',
      'navigationTitle' => 'Countries',
      'requiredRight' => 'ViewCountries',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Countries\\Admin\\Screens\\CountriesArea',
      'path' => 'framework-classes:Application/Countries/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen',
        1 => 'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen',
        2 => 'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen',
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
          'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\CreateScreen',
          'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
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
          'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\ViewScreen',
          'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen',
            1 => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen',
          ),
          'subscreens' => 
          array (
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
              'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\StatusScreen',
              'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
              'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\View\\SettingsScreen',
              'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
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
          'class' => 'Application\\Countries\\Admin\\Screens\\Mode\\ListScreen',
          'path' => 'framework-classes:Application/Countries/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'read-news' => 
    array (
      'id' => 'ReadNewsArea',
      'urlName' => 'read-news',
      'urlPath' => 'read-news',
      'title' => 'AppTestSuite news',
      'navigationTitle' => 'News',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNewsArea',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode',
        1 => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen',
      ),
      'subscreens' => 
      array (
        'article' => 
        array (
          'id' => 'ReadArticleScreen',
          'urlName' => 'article',
          'urlPath' => 'read-news.article',
          'title' => 'News Article',
          'navigationTitle' => 'Article',
          'requiredRight' => 'Login',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ReadArticleScreen',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ReadNews',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'ArticlesListMode',
          'urlName' => 'list',
          'urlPath' => 'read-news.list',
          'title' => 'AppTestSuite news',
          'navigationTitle' => 'News',
          'requiredRight' => 'Login',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\ReadNews\\ArticlesListMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ReadNews',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'welcome' => 
    array (
      'id' => 'WelcomeArea',
      'urlName' => 'welcome',
      'urlPath' => 'welcome',
      'title' => 'Quickstart',
      'navigationTitle' => '',
      'requiredRight' => NULL,
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Admin\\Welcome\\Screens\\WelcomeArea',
      'path' => 'framework-classes:Application/Admin/Welcome/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Admin\\Welcome\\Screens\\OverviewMode',
        1 => 'Application\\Admin\\Welcome\\Screens\\SettingsMode',
      ),
      'subscreens' => 
      array (
        'overview' => 
        array (
          'id' => 'OverviewMode',
          'urlName' => 'overview',
          'urlPath' => 'welcome.overview',
          'title' => 'Quickstart',
          'navigationTitle' => 'Quickstart',
          'requiredRight' => NULL,
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Admin\\Welcome\\Screens\\OverviewMode',
          'path' => 'framework-classes:Application/Admin/Welcome/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'settings' => 
        array (
          'id' => 'SettingsMode',
          'urlName' => 'settings',
          'urlPath' => 'welcome.settings',
          'title' => 'Quickstart settings',
          'navigationTitle' => '',
          'requiredRight' => 'Login',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Admin\\Welcome\\Screens\\SettingsMode',
          'path' => 'framework-classes:Application/Admin/Welcome/Screens',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'media' => 
    array (
      'id' => 'MediaLibraryArea',
      'urlName' => 'media',
      'urlPath' => 'media',
      'title' => 'Media library',
      'navigationTitle' => 'Media',
      'requiredRight' => 'ViewMedia',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Media\\Admin\\Screens\\MediaLibraryArea',
      'path' => 'framework-classes:Application/Media/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Media\\Admin\\Screens\\Mode\\CreateMode',
        1 => 'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode',
        2 => 'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode',
        3 => 'Application\\Media\\Admin\\Screens\\Mode\\ListMode',
        4 => 'Application\\Media\\Admin\\Screens\\Mode\\ViewMode',
      ),
      'subscreens' => 
      array (
        'list' => 
        array (
          'id' => 'ListMode',
          'urlName' => 'list',
          'urlPath' => 'media.list',
          'title' => 'Available media files',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ListMode',
          'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'image-gallery' => 
        array (
          'id' => 'ImageGalleryMode',
          'urlName' => 'image-gallery',
          'urlPath' => 'media.image-gallery',
          'title' => 'Image gallery',
          'navigationTitle' => 'Image gallery',
          'requiredRight' => 'ViewMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ImageGalleryMode',
          'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view' => 
        array (
          'id' => 'ViewMode',
          'urlName' => 'view',
          'urlPath' => 'media.view',
          'title' => 'Media file',
          'navigationTitle' => 'Media file',
          'requiredRight' => 'ViewMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Media\\Admin\\Screens\\Mode\\ViewMode',
          'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
            1 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode',
            2 => 'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode',
          ),
          'subscreens' => 
          array (
            'tagging' => 
            array (
              'id' => 'TagsSubmode',
              'urlName' => 'tagging',
              'urlPath' => 'media.view.tagging',
              'title' => 'Tags',
              'navigationTitle' => 'Tags',
              'requiredRight' => 'EditMedia',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\TagsSubmode',
              'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'status' => 
            array (
              'id' => 'StatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'media.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewMedia',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\StatusSubmode',
              'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'settings' => 
            array (
              'id' => 'SettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'media.view.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewMedia',
              'featureRights' => 
              array (
                'Edit the settings' => 'EditMedia',
              ),
              'class' => 'Application\\Media\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
              'path' => 'framework-classes:Application/Media/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'create' => 
        array (
          'id' => 'CreateMode',
          'urlName' => 'create',
          'urlPath' => 'media.create',
          'title' => 'Add a media file',
          'navigationTitle' => 'Add media',
          'requiredRight' => 'CreateMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Media\\Admin\\Screens\\Mode\\CreateMode',
          'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'settings' => 
        array (
          'id' => 'GlobalSettingsMode',
          'urlName' => 'settings',
          'urlPath' => 'media.settings',
          'title' => 'Media settings',
          'navigationTitle' => 'Media settings',
          'requiredRight' => 'AdminMedia',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Media\\Admin\\Screens\\Mode\\GlobalSettingsMode',
          'path' => 'framework-classes:Application/Media/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
      ),
    ),
    'news' => 
    array (
      'id' => 'ManageNewsArea',
      'urlName' => 'news',
      'urlPath' => 'news',
      'title' => 'Application news central',
      'navigationTitle' => 'News central',
      'requiredRight' => 'Login',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\NewsCentral\\Admin\\Screens\\ManageNewsArea',
      'path' => 'framework-classes:Application/NewsCentral/Admin/Screens',
      'subscreenClasses' => 
      array (
        0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode',
        1 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen',
        2 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen',
        3 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode',
        4 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode',
        5 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode',
        6 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode',
      ),
      'subscreens' => 
      array (
        'view' => 
        array (
          'id' => 'ViewArticleMode',
          'urlName' => 'view',
          'urlPath' => 'news.view',
          'title' => 'View news entry',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticleMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
          'subscreenClasses' => 
          array (
            0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode',
            1 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode',
          ),
          'subscreens' => 
          array (
            'status' => 
            array (
              'id' => 'ArticleStatusSubmode',
              'urlName' => 'status',
              'urlPath' => 'news.view.status',
              'title' => 'Status',
              'navigationTitle' => 'Status',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleStatusSubmode',
              'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewArticle',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'settings' => 
            array (
              'id' => 'ArticleSettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'news.view.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
                'Modify the settings' => 'EditNews',
              ),
              'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewArticle\\ArticleSettingsSubmode',
              'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewArticle',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'create-category' => 
        array (
          'id' => 'CreateCategoryMode',
          'urlName' => 'create-category',
          'urlPath' => 'news.create-category',
          'title' => 'Create a news category',
          'navigationTitle' => 'Create Category',
          'requiredRight' => 'EditNews',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateCategoryMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
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
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateArticleScreen',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'list' => 
        array (
          'id' => 'NewsListMode',
          'urlName' => 'list',
          'urlPath' => 'news.list',
          'title' => 'Available news articles',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\NewsListMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'categories-list' => 
        array (
          'id' => 'CategoriesListMode',
          'urlName' => 'categories-list',
          'urlPath' => 'news.categories-list',
          'title' => 'Available categories',
          'navigationTitle' => 'Overview',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CategoriesListMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
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
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\CreateAlertScreen',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view-category' => 
        array (
          'id' => 'ViewCategoryMode',
          'urlName' => 'view-category',
          'urlPath' => 'news.view-category',
          'title' => 'View news category',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewNews',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategoryMode',
          'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews',
          'subscreenClasses' => 
          array (
            0 => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode',
          ),
          'subscreens' => 
          array (
            'settings' => 
            array (
              'id' => 'CategorySettingsSubmode',
              'urlName' => 'settings',
              'urlPath' => 'news.view-category.settings',
              'title' => 'Settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewNews',
              'featureRights' => 
              array (
                'Modify the settings' => 'EditNews',
              ),
              'class' => 'Application\\NewsCentral\\Admin\\Screens\\Mode\\ViewCategory\\CategorySettingsSubmode',
              'path' => 'framework-classes:Application/NewsCentral/Admin/Screens/ManageNews/ViewCategory',
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
    'tags' => 
    array (
      'id' => 'TagsArea',
      'urlName' => 'tags',
      'urlPath' => 'tags',
      'title' => 'Tags',
      'navigationTitle' => 'Tags',
      'requiredRight' => 'ViewTags',
      'featureRights' => 
      array (
      ),
      'class' => 'Application\\Tags\\Admin\\Screens\\Area\\TagsArea',
      'path' => 'framework-classes:Application/Tags/Admin/Screens/Area',
      'subscreenClasses' => 
      array (
        0 => 'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode',
        1 => 'Application\\Tags\\Admin\\Screens\\Mode\\ListMode',
        2 => 'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode',
      ),
      'subscreens' => 
      array (
        'list' => 
        array (
          'id' => 'ListMode',
          'urlName' => 'list',
          'urlPath' => 'tags.list',
          'title' => 'Available root tags',
          'navigationTitle' => 'List',
          'requiredRight' => 'ViewTags',
          'featureRights' => 
          array (
            'Delete tags' => 'DeleteTags',
          ),
          'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\ListMode',
          'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
          ),
        ),
        'view-tag' => 
        array (
          'id' => 'ViewMode',
          'urlName' => 'view-tag',
          'urlPath' => 'tags.view-tag',
          'title' => 'View a tag',
          'navigationTitle' => 'View',
          'requiredRight' => 'ViewTags',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\ViewMode',
          'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
            0 => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
            1 => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode',
          ),
          'subscreens' => 
          array (
            'tag-tree' => 
            array (
              'id' => 'TagTreeSubmode',
              'urlName' => 'tag-tree',
              'urlPath' => 'tags.view-tag.tag-tree',
              'title' => 'Tag tree',
              'navigationTitle' => 'Tree',
              'requiredRight' => 'EditTags',
              'featureRights' => 
              array (
              ),
              'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\TagTreeSubmode',
              'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
            'tag-settings' => 
            array (
              'id' => 'SettingsSubmode',
              'urlName' => 'tag-settings',
              'urlPath' => 'tags.view-tag.tag-settings',
              'title' => 'Edit tag settings',
              'navigationTitle' => 'Settings',
              'requiredRight' => 'ViewTags',
              'featureRights' => 
              array (
                'Edit the settings' => 'EditTags',
              ),
              'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\View\\SettingsSubmode',
              'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode/View',
              'subscreenClasses' => 
              array (
              ),
              'subscreens' => 
              array (
              ),
            ),
          ),
        ),
        'create' => 
        array (
          'id' => 'CreateMode',
          'urlName' => 'create',
          'urlPath' => 'tags.create',
          'title' => 'Create a tag',
          'navigationTitle' => 'Create tag',
          'requiredRight' => 'CreateTags',
          'featureRights' => 
          array (
          ),
          'class' => 'Application\\Tags\\Admin\\Screens\\Mode\\CreateMode',
          'path' => 'framework-classes:Application/Tags/Admin/Screens/Mode',
          'subscreenClasses' => 
          array (
          ),
          'subscreens' => 
          array (
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
      'featureRights' => 
      array (
      ),
      'class' => 'TestDriver\\Area\\QuickNavScreen',
      'path' => 'driver-classes:Area',
      'subscreenClasses' => 
      array (
      ),
      'subscreens' => 
      array (
      ),
    ),
  ),
);
