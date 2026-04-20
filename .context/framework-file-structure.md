# Application Framework - Class File Structure
_SOURCE: Tree of PHP Class Files_
# Tree of PHP Class Files
###  
```
└── src/
    └── classes/
        └── AppFactory.php
        └── Application/
            ├── AI/
            │   ├── AIToolException.php
            │   ├── BaseAIToolContainer.php
            │   ├── Cache/
            │   │   ├── AICacheLocation.php
            │   │   ├── AICacheStrategyInterface.php
            │   │   ├── BaseAICacheStrategy.php
            │   │   ├── Events/
            │   │   │   ├── RegisterAIIndexCacheListener.php
            │   │   ├── Strategies/
            │   │   │   └── FixedDurationStrategy.php
            │   │   │   └── UncachedStrategy.php
            │   ├── EnvironmentRunner.php
            │   ├── README.md
            │   ├── Server/
            │   │   ├── FrameworkMCPServer.php
            │   │   ├── StderrLogger.php
            │   ├── Tools/
            │   │   ├── AIToolInterface.php
            │   │   ├── BaseAITool.php
            │   ├── module-context.yaml
            ├── API/
            │   ├── APIException.php
            │   ├── APIFoldersManager.php
            │   ├── APIManager.php
            │   ├── APIMethodInterface.php
            │   ├── APIResponseDataException.php
            │   ├── APIUrls.php
            │   ├── Admin/
            │   │   ├── APIClientRecordURLs.php
            │   │   ├── APICollectionURLs.php
            │   │   ├── APIKeyCollectionURLs.php
            │   │   ├── APIKeyURLs.php
            │   │   ├── APIScreenRights.php
            │   │   ├── RequestTypes/
            │   │   │   ├── APIClientRequestInterface.php
            │   │   │   ├── APIClientRequestTrait.php
            │   │   │   ├── APIClientRequestType.php
            │   │   ├── Screens/
            │   │   │   ├── APIClientsArea.php
            │   │   │   ├── Mode/
            │   │   │   │   └── ClientsListMode.php
            │   │   │   │   └── CreateClientMode.php
            │   │   │   │   └── View/
            │   │   │   │       ├── APIKeysSubmode.php
            │   │   │   │       ├── ClientSettingsSubmode.php
            │   │   │   │       ├── ClientStatusSubmode.php
            │   │   │   │   └── ViewClientMode.php
            │   │   ├── Traits/
            │   │   │   └── APIKeyActionInterface.php
            │   │   │   └── APIKeyActionRecordInterface.php
            │   │   │   └── APIKeyActionRecordTrait.php
            │   │   │   └── APIKeyActionTrait.php
            │   │   │   └── ClientModeInterface.php
            │   │   │   └── ClientModeTrait.php
            │   │   │   └── ClientSubmodeInterface.php
            │   │   │   └── ClientSubmodeTrait.php
            │   ├── BaseMethods/
            │   │   ├── BaseAPIMethod.php
            │   ├── Cache/
            │   │   ├── APICacheException.php
            │   │   ├── APICacheManager.php
            │   │   ├── APICacheStrategyInterface.php
            │   │   ├── APIResponseCacheLocation.php
            │   │   ├── CacheableAPIMethodInterface.php
            │   │   ├── CacheableAPIMethodTrait.php
            │   │   ├── README.md
            │   │   ├── Strategies/
            │   │   │   ├── FixedDurationStrategy.php
            │   │   │   ├── ManualOnlyStrategy.php
            │   │   ├── UserScopedCacheInterface.php
            │   │   ├── UserScopedCacheTrait.php
            │   │   ├── module-context.yaml
            │   ├── Clients/
            │   │   ├── API/
            │   │   │   ├── APIKeyMethodInterface.php
            │   │   │   ├── APIKeyMethodTrait.php
            │   │   │   ├── Params/
            │   │   │   │   └── APIKeyHandler.php
            │   │   │   │   └── APIKeyParam.php
            │   │   ├── APIClientException.php
            │   │   ├── APIClientFilterCriteria.php
            │   │   ├── APIClientFilterSettings.php
            │   │   ├── APIClientRecord.php
            │   │   ├── APIClientRecordSettings.php
            │   │   ├── APIClientsCollection.php
            │   │   ├── Keys/
            │   │   │   ├── APIKeyException.php
            │   │   │   ├── APIKeyFilterCriteria.php
            │   │   │   ├── APIKeyFilterSettings.php
            │   │   │   ├── APIKeyMethods.php
            │   │   │   ├── APIKeyRecord.php
            │   │   │   ├── APIKeyRecordSettings.php
            │   │   │   ├── APIKeysCollection.php
            │   │   ├── README.md
            │   │   ├── module-context.yaml
            │   ├── Collection/
            │   │   ├── APICacheLocation.php
            │   │   ├── APIMethodCollection.php
            │   │   ├── APIMethodIndex.php
            │   ├── Connector/
            │   │   ├── AppAPIConnector.php
            │   │   ├── AppAPIMethod.php
            │   ├── Documentation/
            │   │   ├── APIDocumentation.php
            │   │   ├── BaseAPIDocumentation.php
            │   │   ├── Examples/
            │   │   │   ├── JSONMethodExample.php
            │   │   ├── MethodDocumentation.php
            │   ├── ErrorResponse.php
            │   ├── ErrorResponsePayload.php
            │   ├── Events/
            │   │   ├── RegisterAPIIndexCacheListener.php
            │   │   ├── RegisterAPIResponseCacheListener.php
            │   ├── Groups/
            │   │   ├── APIGroupInterface.php
            │   │   ├── FrameworkAPIGroup.php
            │   │   ├── GenericAPIGroup.php
            │   ├── OpenAPI/
            │   │   ├── GetOpenAPISpec.php
            │   │   ├── HtaccessGenerator.php
            │   │   ├── MethodConverter.php
            │   │   ├── OpenAPIGenerator.php
            │   │   ├── OpenAPISchema.php
            │   │   ├── ParameterConverter.php
            │   │   ├── README.md
            │   │   ├── ResponseConverter.php
            │   │   ├── SchemaInferrer.php
            │   │   ├── TypeMapper.php
            │   │   ├── module-context.yaml
            │   ├── Parameters/
            │   │   ├── APIParamManager.php
            │   │   ├── APIParameterException.php
            │   │   ├── APIParameterInterface.php
            │   │   ├── BaseAPIParameter.php
            │   │   ├── CommonTypes/
            │   │   │   ├── AliasParameter.php
            │   │   │   ├── AlphabeticalParameter.php
            │   │   │   ├── AlphanumericParameter.php
            │   │   │   ├── DateParameter.php
            │   │   │   ├── EmailParameter.php
            │   │   │   ├── LabelParameter.php
            │   │   │   ├── MD5Parameter.php
            │   │   │   ├── NameOrTitleParameter.php
            │   │   ├── Flavors/
            │   │   │   ├── APIHeaderParameterInterface.php
            │   │   │   ├── APIHeaderParameterTrait.php
            │   │   │   ├── RequiredOnlyParamInterface.php
            │   │   │   ├── RequiredOnlyParamTrait.php
            │   │   ├── Handlers/
            │   │   │   ├── APIHandlerInterface.php
            │   │   │   ├── BaseAPIHandler.php
            │   │   │   ├── BaseParamHandler.php
            │   │   │   ├── BaseParamsHandlerContainer.php
            │   │   │   ├── BaseRuleHandler.php
            │   │   │   ├── ParamHandlerInterface.php
            │   │   │   ├── ParamsHandlerContainerInterface.php
            │   │   │   ├── RuleHandlerInterface.php
            │   │   ├── ParamTypeSelector.php
            │   │   ├── README.md
            │   │   ├── Reserved/
            │   │   │   ├── APIMethodParameter.php
            │   │   │   ├── APIVersionParameter.php
            │   │   ├── ReservedParamInterface.php
            │   │   ├── Rules/
            │   │   │   ├── BaseCustomParamSet.php
            │   │   │   ├── BaseRule.php
            │   │   │   ├── CustomParamSetInterface.php
            │   │   │   ├── ParamSet.php
            │   │   │   ├── ParamSetInterface.php
            │   │   │   ├── RuleInterface.php
            │   │   │   ├── RuleTypeSelector.php
            │   │   │   ├── Type/
            │   │   │   │   └── OrRule.php
            │   │   │   │   └── RequiredIfOtherIsSetRule.php
            │   │   │   │   └── RequiredIfOtherValueEquals.php
            │   │   ├── Type/
            │   │   │   ├── BooleanParameter.php
            │   │   │   ├── IDListParameter.php
            │   │   │   ├── IntegerParameter.php
            │   │   │   ├── JSONParameter.php
            │   │   │   ├── StringParam/
            │   │   │   │   ├── StringValidations.php
            │   │   │   ├── StringParameter.php
            │   │   ├── Validation/
            │   │   │   ├── BaseParamValidation.php
            │   │   │   ├── ParamValidationInterface.php
            │   │   │   ├── ParamValidationResults.php
            │   │   │   ├── Type/
            │   │   │   │   └── CallbackValidation.php
            │   │   │   │   └── EnumValidation.php
            │   │   │   │   └── RegexValidation.php
            │   │   │   │   └── RequiredValidation.php
            │   │   │   │   └── ValueExistsCallbackValidation.php
            │   │   ├── ValueLookup/
            │   │   │   ├── SelectableParamValue.php
            │   │   │   ├── SelectableValueParamInterface.php
            │   │   │   ├── SelectableValueParamTrait.php
            │   │   ├── module-context.yaml
            │   ├── README.md
            │   ├── Response/
            │   │   ├── JSONInfoSerializer.php
            │   │   ├── ResponseInterface.php
            │   ├── ResponsePayload.php
            │   ├── Traits/
            │   │   ├── DryRun/
            │   │   │   ├── DryRunAPIParam.php
            │   │   ├── DryRunAPIInterface.php
            │   │   ├── DryRunAPITrait.php
            │   │   ├── JSONRequestInterface.php
            │   │   ├── JSONRequestTrait.php
            │   │   ├── JSONResponseInterface.php
            │   │   ├── JSONResponseTrait.php
            │   │   ├── JSONResponseWithExampleInterface.php
            │   │   ├── JSONResponseWithExampleTrait.php
            │   │   ├── RequestRequestInterface.php
            │   │   ├── RequestRequestTrait.php
            │   ├── User/
            │   │   ├── APIRightsInterface.php
            │   │   ├── APIRightsTrait.php
            │   ├── Utilities/
            │   │   ├── KeyDescription.php
            │   │   ├── KeyPath.php
            │   │   ├── KeyPathInterface.php
            │   │   ├── KeyReplacement.php
            │   ├── Versioning/
            │   │   ├── APIVersionInterface.php
            │   │   ├── BaseAPIVersion.php
            │   │   ├── VersionCollection.php
            │   │   ├── VersionedAPIInterface.php
            │   │   ├── VersionedAPITrait.php
            │   ├── module-context.yaml
            ├── Admin/
            │   ├── AdminException.php
            │   ├── AdminScreenStubInterface.php
            │   ├── Area/
            │   │   ├── BaseMode.php
            │   │   ├── Events/
            │   │   │   ├── UIHandlingCompleteEvent.php
            │   │   ├── Mode/
            │   │   │   └── BaseSubmode.php
            │   │   │   └── Submode/
            │   │   │       └── BaseAction.php
            │   ├── BaseArea.php
            │   ├── BaseScreenRights.php
            │   ├── ClassLoaderScreenInterface.php
            │   ├── ClassLoaderScreenTrait.php
            │   ├── Index/
            │   │   ├── API/
            │   │   │   ├── DescribeAdminAreasAPIInterface.php
            │   │   │   ├── Methods/
            │   │   │   │   └── DescribeAdminAreasAPI.php
            │   │   ├── AdminScreenIndex.php
            │   │   ├── AdminScreenIndexer.php
            │   │   ├── AdminScreenInfoCollector.php
            │   │   ├── ScreenDataInterface.php
            │   │   ├── Screens/
            │   │   │   ├── SitemapMode.php
            │   │   ├── StubArea.php
            │   │   ├── StubMode.php
            │   │   ├── StubSubmode.php
            │   ├── RequestTypes/
            │   │   ├── BaseRequestType.php
            │   │   ├── RequestTypeInterface.php
            │   ├── ScreenException.php
            │   ├── ScreenRightsContainerInterface.php
            │   ├── ScreenRightsContainerTrait.php
            │   ├── ScreenRightsInterface.php
            │   ├── Screens/
            │   │   ├── Events/
            │   │   │   └── ActionsHandledEvent.php
            │   │   │   └── BaseScreenEvent.php
            │   │   │   └── BeforeActionsHandledEvent.php
            │   │   │   └── BeforeBreadcrumbHandledEvent.php
            │   │   │   └── BeforeContentRenderedEvent.php
            │   │   │   └── BeforeSidebarHandledEvent.php
            │   │   │   └── BreadcrumbHandledEvent.php
            │   │   │   └── ContentRenderedEvent.php
            │   │   │   └── SidebarHandledEvent.php
            │   ├── Skeleton.php
            │   ├── Traits/
            │   │   ├── DevelModeInterface.php
            │   │   ├── DevelModeTrait.php
            │   ├── URL.php
            │   ├── Welcome/
            │   │   ├── Events/
            │   │   │   ├── BaseWelcomeQuickNavListener.php
            │   │   │   ├── WelcomeQuickNavEvent.php
            │   │   ├── Screens/
            │   │   │   ├── OverviewMode.php
            │   │   │   ├── SettingsMode.php
            │   │   │   ├── WelcomeArea.php
            │   │   ├── WelcomeManager.php
            │   ├── Wizard/
            │   │   ├── BaseWizardMode.php
            │   │   ├── InvalidationHandler.php
            │   │   ├── Step.php
            │   ├── WizardException.php
            ├── Ajax/
            │   ├── AjaxException.php
            │   ├── AjaxHandler.php
            │   ├── AjaxMethod.php
            │   ├── AjaxMethodInterface.php
            │   ├── BaseCustomPropertiesMethod.php
            │   ├── BaseHTMLAjaxMethod.php
            │   ├── BaseJSONAjaxMethod.php
            ├── AjaxMethods/
            │   ├── AddFeedbackMethod.php
            │   ├── AddJSErrorLog.php
            │   ├── AddMessage.php
            │   ├── CustomPropertyAdd.php
            │   ├── CustomPropertyDelete.php
            │   ├── CustomPropertySave.php
            │   ├── DeleteListFilter.php
            │   ├── GetChangelogRevisions.php
            │   ├── GetGridFullViewHTML.php
            │   ├── GetGridFullViewHTML/
            │   │   ├── Grid.php
            │   ├── GetIconsReference.php
            │   ├── GetLookupItems.php
            │   ├── GetScriptLoadKey.php
            │   ├── GetWhatsnew.php
            │   ├── KeepAlive.php
            │   ├── LockingGetStatus.php
            │   ├── LockingKeepAlive.php
            │   ├── LockingReleaseLock.php
            │   ├── LookupItems.php
            │   ├── NoAjaxHandlerFoundMethod.php
            │   ├── NotepadAdd.php
            │   ├── NotepadDelete.php
            │   ├── NotepadGet.php
            │   ├── NotepadGetIDs.php
            │   ├── NotepadPin.php
            │   ├── NotepadSave.php
            │   ├── ProcessMediaDocument.php
            │   ├── RatingAdd.php
            │   ├── RatingSetComments.php
            │   ├── SaveListFilter.php
            │   ├── Tagging/
            │   │   ├── GetTaggableInfoMethod.php
            │   │   ├── SetTaggableTags.php
            │   ├── ThrowError.php
            │   ├── Transliterate.php
            ├── AppFactory/
            │   ├── AppFactoryException.php
            │   ├── ClassCacheHandler.php
            │   ├── ClassCacheLocation.php
            │   ├── ClassFinder.php
            ├── AppSets/
            │   ├── Admin/
            │   │   ├── AppSetAdminURLs.php
            │   │   ├── AppSetScreenRights.php
            │   │   ├── Screens/
            │   │   │   ├── AppSetsDevelMode.php
            │   │   │   ├── Submode/
            │   │   │   │   └── CreateSetSubmode.php
            │   │   │   │   └── SetsListSubmode.php
            │   │   │   │   └── View/
            │   │   │   │       ├── DeleteAction.php
            │   │   │   │       ├── DocumentationAction.php
            │   │   │   │       ├── SettingsAction.php
            │   │   │   │       ├── StatusAction.php
            │   │   │   │   └── ViewSubmode.php
            │   │   ├── Traits/
            │   │   │   └── SubmodeInterface.php
            │   │   │   └── SubmodeTrait.php
            │   │   │   └── ViewActionInterface.php
            │   │   │   └── ViewActionTrait.php
            │   ├── AppSet.php
            │   ├── AppSetSettingsManager.php
            │   ├── AppSetsCollection.php
            │   ├── AppSetsException.php
            │   ├── AppSetsFilterCriteria.php
            │   ├── AppSetsFilterSettings.php
            │   ├── DefaultAppSet.php
            │   ├── README.md
            │   ├── module-context.yaml
            ├── AppSettings/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── AppSettingsDevelMode.php
            │   ├── AppSettingDef.php
            │   ├── AppSettingRecord.php
            │   ├── AppSettingsFilterCriteria.php
            │   ├── AppSettingsFilterSettings.php
            │   ├── AppSettingsRegistry.php
            │   ├── Events/
            │   │   └── BaseRegisterAppSettingsListener.php
            │   │   └── RegisterAppSettingsEvent.php
            ├── Application.php
            ├── Bootstrap/
            │   ├── BootException.php
            │   ├── Bootstrap.php
            │   ├── CLIScreen.php
            │   ├── Screen.php
            │   ├── Screen/
            │   │   └── AIToolsBootstrap.php
            │   │   └── APIBootstrap.php
            │   │   └── APIDocumentationBootstrap.php
            │   │   └── Ajax.php
            │   │   └── AjaxError.php
            │   │   └── Changelog.php
            │   │   └── ComposerScriptBootstrap.php
            │   │   └── Cronjobs.php
            │   │   └── DeployCallbackBootstrap.php
            │   │   └── Documentation.php
            │   │   └── HealthMonitor.php
            │   │   └── Installer.php
            │   │   └── LocalizedStrings.php
            │   │   └── LoggedOut.php
            │   │   └── Main.php
            │   │   └── Media.php
            │   │   └── RequestLog.php
            │   │   └── TestSuiteBootstrap.php
            │   │   └── Updaters.php
            │   │   └── Upload.php
            ├── CORS/
            │   ├── CORS.php
            ├── CacheControl/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── CacheControlMode.php
            │   ├── BaseCacheLocation.php
            │   ├── CacheLocationInterface.php
            │   ├── CacheManager.php
            │   ├── CacheManagerException.php
            │   ├── Events/
            │   │   └── BaseRegisterCacheLocationsListener.php
            │   │   └── RegisterCacheLocationsEvent.php
            │   │   └── RegisterClassCacheListener.php
            ├── Changelog/
            │   ├── BaseChangelogHandler.php
            │   ├── Changelog.php
            │   ├── Entry.php
            │   ├── Event/
            │   │   ├── ChangelogCommittedEvent.php
            │   ├── FilterCriteria.php
            ├── Collection/
            │   ├── Admin/
            │   │   ├── BaseRecordSelectionTieIn.php
            │   │   ├── RecordSelectionTieInInterface.php
            │   ├── BaseCollectionInterface.php
            │   ├── BaseRecord.php
            │   ├── BaseRecord/
            │   │   ├── IntegerPrimary.php
            │   ├── CollectionException.php
            │   ├── CollectionItemInterface.php
            │   ├── IntegerCollectionInterface.php
            │   ├── IntegerCollectionItemInterface.php
            │   ├── StringCollectionInterface.php
            │   ├── StringCollectionItemInterface.php
            ├── Composer/
            │   ├── BuildMessages.php
            │   ├── CSSClassesGenerator.php
            │   ├── ComposerScripts.php
            │   ├── IconBuilder/
            │   │   ├── AbstractLanguageRenderer.php
            │   │   ├── IconBuilder.php
            │   │   ├── IconDefinition.php
            │   │   ├── IconsReader.php
            │   │   ├── JSRenderer.php
            │   │   ├── PHPRenderer.php
            │   ├── KeywordGlossary/
            │   │   ├── Events/
            │   │   │   ├── BaseDecorateGlossaryListener.php
            │   │   │   ├── DecorateGlossaryEvent.php
            │   │   ├── GlossarySection.php
            │   │   ├── GlossarySectionEntry.php
            │   │   ├── KeywordEntry.php
            │   │   ├── KeywordGlossaryBuilder.php
            │   │   ├── KeywordGlossaryGenerator.php
            │   │   ├── KeywordGlossaryRenderer.php
            │   │   ├── KeywordParser.php
            │   ├── ModulesOverview/
            │   │   ├── ModuleContextFileFinder.php
            │   │   ├── ModuleInfo.php
            │   │   ├── ModuleInfoParser.php
            │   │   ├── ModuleJsonExportGenerator.php
            │   │   ├── ModulesOverviewGenerator.php
            │   │   ├── ModulesOverviewRenderer.php
            │   │   ├── ReadmeOverviewParser.php
            │   ├── README.md
            │   ├── module-context.yaml
            ├── ConfigSettings/
            │   ├── AppConfig.php
            │   ├── BaseConfigRegistry.php
            │   ├── ConfigException.php
            │   ├── SetAppConfigSettingTrait.php
            │   ├── SetConfigSettingInterface.php
            ├── Countries/
            │   ├── AI/
            │   │   ├── CountryAIException.php
            │   │   ├── CountryAITools.php
            │   │   ├── Tools/
            │   │   │   └── GetCountryConfigTool.php
            │   │   │   └── ListCountriesTool.php
            │   ├── API/
            │   │   ├── AppCountryAPIInterface.php
            │   │   ├── AppCountryAPITrait.php
            │   │   ├── AppCountryParamsContainer.php
            │   │   ├── CountriesAPIGroup.php
            │   │   ├── CountryAPIException.php
            │   │   ├── Methods/
            │   │   │   ├── GetAppCountriesAPI.php
            │   │   ├── ParamSets/
            │   │   │   ├── AppCountryParamRule.php
            │   │   │   ├── AppCountryParamSetInterface.php
            │   │   │   ├── AppCountryRuleHandler.php
            │   │   │   ├── BaseAppCountryParamSet.php
            │   │   │   ├── CountryIDSet.php
            │   │   │   ├── CountryISOSet.php
            │   │   ├── Params/
            │   │   │   └── AppCountryIDHandler.php
            │   │   │   └── AppCountryIDParam.php
            │   │   │   └── AppCountryISOHandler.php
            │   │   │   └── AppCountryISOParam.php
            │   │   │   └── AppCountryParamInterface.php
            │   ├── Admin/
            │   │   ├── CountryAdminURLs.php
            │   │   ├── CountryRequestType.php
            │   │   ├── CountryScreens.php
            │   │   ├── MainAdminURLs.php
            │   │   ├── Screens/
            │   │   │   ├── CountriesArea.php
            │   │   │   ├── Mode/
            │   │   │   │   └── Create/
            │   │   │   │       ├── BaseCreateStep.php
            │   │   │   │       ├── ConfirmStep.php
            │   │   │   │       ├── CountrySettingsStep.php
            │   │   │   │       ├── CreateWizardException.php
            │   │   │   │       ├── SourceCountrySelectionStep.php
            │   │   │   │   └── CreateScreen.php
            │   │   │   │   └── ListScreen.php
            │   │   │   │   └── View/
            │   │   │   │       ├── SettingsScreen.php
            │   │   │   │       ├── StatusScreen.php
            │   │   │   │   └── ViewScreen.php
            │   │   ├── Traits/
            │   │   │   └── CountryModeInterface.php
            │   │   │   └── CountryModeTrait.php
            │   │   │   └── CountryRequestInterface.php
            │   │   │   └── CountryRequestTrait.php
            │   │   │   └── CountryViewInterface.php
            │   │   │   └── CountryViewTrait.php
            │   ├── ButtonBar.php
            │   ├── Countries.php
            │   ├── CountriesCollection.php
            │   ├── Country.php
            │   ├── Country/
            │   │   ├── Icon.php
            │   ├── CountryException.php
            │   ├── CountrySettingsManager.php
            │   ├── Event/
            │   │   ├── IgnoredCountriesUpdatedEvent.php
            │   ├── FilterCriteria.php
            │   ├── FilterSettings.php
            │   ├── LocaleCode.php
            │   ├── Navigator.php
            │   ├── Rights/
            │   │   ├── CountryRightsInterface.php
            │   │   ├── CountryRightsTrait.php
            │   │   ├── CountryScreenRights.php
            │   ├── Selector.php
            ├── CustomProperties/
            │   ├── CustomProperties.php
            │   ├── Presets/
            │   │   ├── PropertyPresetRecord.php
            │   │   ├── PropertyPresetsCollection.php
            │   │   ├── PropertyPresetsFilterCriteria.php
            │   │   ├── PropertyPresetsFilterSettings.php
            │   ├── Property.php
            │   ├── PropertyFilterCriteria.php
            │   ├── PropertyFilterSettings.php
            ├── DBDumps/
            │   ├── DBDumps.php
            │   ├── Dump.php
            ├── DeploymentRegistry/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── DeploymentHistoryMode.php
            │   ├── BaseDeployTask.php
            │   ├── DeploymentInfo.php
            │   ├── DeploymentRegistry.php
            │   ├── DeploymentRegistryException.php
            │   ├── DeploymentTaskInterface.php
            │   ├── Tasks/
            │   │   └── ClearClassCacheTask.php
            │   │   └── StoreCurrentVersionTask.php
            │   │   └── StoreDeploymentInfoTask.php
            │   │   └── WriteLocalizationFilesTask.php
            ├── Development/
            │   ├── Admin/
            │   │   ├── AppDevAdminURLs.php
            │   │   ├── DevScreenRights.php
            │   │   ├── Screens/
            │   │   │   └── DatabaseDumpDevMode.php
            │   │   │   └── DevelArea.php
            │   │   │   └── DevelOverviewMode.php
            │   ├── DevManager.php
            │   ├── Events/
            │   │   └── DisplayAppConfigEvent.php
            ├── Disposables/
            │   ├── Attributes/
            │   │   ├── DisposedAware.php
            │   ├── DisposableDisposedException.php
            │   ├── DisposableInterface.php
            │   ├── DisposableTrait.php
            │   ├── Event/
            │   │   └── DisposedEvent.php
            ├── Driver/
            │   ├── DevChangelog.php
            │   ├── Driver.php
            │   ├── DriverException.php
            │   ├── DriverSettings.php
            │   ├── Events/
            │   │   ├── DriverInstantiatedEvent.php
            │   ├── Interface.php
            │   ├── Storage.php
            │   ├── Storage/
            │   │   ├── DB.php
            │   │   ├── File.php
            │   ├── VersionInfo.php
            ├── Environments/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── AppConfigMode.php
            │   ├── BaseEnvironmentsConfig.php
            │   ├── Environment.php
            │   ├── Environment/
            │   │   ├── Requirement.php
            │   │   ├── Requirement/
            │   │   │   └── BoolTrue.php
            │   │   │   └── CLI.php
            │   │   │   └── HostNameContains.php
            │   │   │   └── LocalTest.php
            │   │   │   └── Windows.php
            │   ├── EnvironmentException.php
            │   ├── Environments.php
            │   ├── EnvironmentsConfig/
            │   │   ├── BaseEnvironmentConfig.php
            │   ├── Events/
            │   │   └── BaseDisplayAppConfigListener.php
            │   │   └── EnvironmentActivated.php
            │   │   └── EnvironmentDetected.php
            │   │   └── IncludesLoaded.php
            ├── ErrorDetails/
            │   ├── ExceptionPageRenderer.php
            │   ├── ExceptionRenderer.php
            │   ├── ThemeFile.php
            │   ├── ThemeLocation.php
            ├── ErrorLog/
            │   ├── Admin/
            │   │   ├── ErrorLogScreenRights.php
            │   │   ├── Screens/
            │   │   │   ├── ErrorLogMode.php
            │   │   │   ├── ListSubmode.php
            │   │   │   ├── ViewSubmode.php
            │   │   ├── Traits/
            │   │   │   └── ErrorLogSubmodeInterface.php
            │   │   │   └── ErrorLogSubmodeTrait.php
            │   ├── ErrorLog.php
            │   ├── ErrorLogException.php
            │   ├── Log.php
            │   ├── Log/
            │   │   └── Entry.php
            │   │   └── Entry/
            │   │       └── AJAX.php
            │   │       └── Exception.php
            │   │       └── General.php
            │   │       └── JavaScript.php
            ├── EventHandler/
            │   ├── Event/
            │   │   ├── BaseEvent.php
            │   │   ├── EventInterface.php
            │   │   ├── EventListener.php
            │   │   ├── StandardEvent.php
            │   ├── EventHandlingException.php
            │   ├── EventManager.php
            │   ├── Eventables/
            │   │   ├── BaseEventableEvent.php
            │   │   ├── EventableEventInterface.php
            │   │   ├── EventableException.php
            │   │   ├── EventableInterface.php
            │   │   ├── EventableListener.php
            │   │   ├── EventableTrait.php
            │   │   ├── StandardEventableEvent.php
            │   ├── OfflineEvents/
            │   │   ├── BaseOfflineEvent.php
            │   │   ├── BaseOfflineListener.php
            │   │   ├── Index/
            │   │   │   ├── EventClassFinder.php
            │   │   │   ├── EventIndex.php
            │   │   │   ├── EventIndexer.php
            │   │   │   ├── ListenerClassFinder.php
            │   │   ├── OfflineEventContainer.php
            │   │   ├── OfflineEventException.php
            │   │   ├── OfflineEventInterface.php
            │   │   ├── OfflineEventListenerInterface.php
            │   │   ├── OfflineEventsManager.php
            │   ├── README.md
            │   ├── Traits/
            │   │   ├── HTMLProcessingEventInterface.php
            │   │   ├── HTMLProcessingEventTrait.php
            │   ├── module-context.yaml
            ├── Events/
            │   ├── ApplicationStartedEvent.php
            │   ├── SystemShutDownEvent.php
            ├── Exception/
            │   ├── ApplicationException.php
            │   ├── UnexpectedInstanceException.php
            ├── Feedback/
            │   ├── FeedbackCollection.php
            │   ├── FeedbackFilterCriteria.php
            │   ├── FeedbackFilterSettings.php
            │   ├── FeedbackRecord.php
            ├── FilterCriteria/
            │   ├── Database.php
            │   ├── Database/
            │   │   ├── ColumnUsage.php
            │   │   ├── CustomColumn.php
            │   │   ├── Join.php
            │   ├── DatabaseExtended.php
            │   ├── Events/
            │   │   ├── ApplyFiltersEvent.php
            │   ├── FilterCriteria.php
            │   ├── FilterCriteriaDBExtendedInterface.php
            │   ├── FilterCriteriaDBInterface.php
            │   ├── FilterCriteriaException.php
            │   ├── Items/
            │   │   ├── BaseIntegerItem.php
            │   │   ├── BaseStringItem.php
            │   │   ├── GenericIntegerItem.php
            │   │   ├── GenericStringItem.php
            │   ├── RevisionableRevisions.php
            ├── FilterSettings/
            │   ├── DateParser.php
            │   ├── FilterSettings.php
            │   ├── FilterSettingsException.php
            │   ├── FilterSettingsInterface.php
            │   ├── SettingDef.php
            ├── Formable/
            │   ├── Container.php
            │   ├── Event/
            │   │   ├── BaseFormableEvent.php
            │   │   ├── ClientFormRenderedEvent.php
            │   ├── Exception.php
            │   ├── Formable.php
            │   ├── Generic.php
            │   ├── Header.php
            │   ├── RecordSelector.php
            │   ├── RecordSelector/
            │   │   ├── Entry.php
            │   ├── RecordSettings.php
            │   ├── RecordSettings/
            │   │   ├── Extended.php
            │   │   ├── Group.php
            │   │   ├── Setting.php
            │   │   ├── ValueSet.php
            │   ├── Selector.php
            ├── Framework/
            │   ├── AppFolder.php
            │   ├── AppFramework.php
            │   ├── PackageInfo.php
            ├── HealthMonitor/
            │   ├── Component.php
            │   ├── Component/
            │   │   ├── AdminAPI.php
            │   │   ├── Database.php
            │   │   ├── Filesystem.php
            │   │   ├── PHP.php
            │   ├── HealthMonitor.php
            ├── Installer/
            │   ├── Installer.php
            │   ├── Task.php
            │   ├── Task/
            │   │   └── InitSystemUsers.php
            ├── Interfaces/
            │   ├── Admin/
            │   │   ├── AdminActionInterface.php
            │   │   ├── AdminAreaInterface.php
            │   │   ├── AdminModeInterface.php
            │   │   ├── AdminScreenInterface.php
            │   │   ├── AdminSubmodeInterface.php
            │   │   ├── LockableScreen.php
            │   │   ├── MissingRecordInterface.php
            │   │   ├── RequestTypes/
            │   │   │   ├── RequestCountryInterface.php
            │   │   ├── ScreenAccessInterface.php
            │   │   ├── ScreenDisplayMode.php
            │   │   ├── Wizard/
            │   │   │   ├── Step.php
            │   │   │   ├── Step/
            │   │   │   │   ├── Confirmation.php
            │   │   │   │   ├── CreateDBRecordStep.php
            │   │   │   │   ├── SelectCountryStep.php
            │   │   │   │   ├── SettingsManagerStep.php
            │   │   │   ├── WithConfirmationStep.php
            │   │   │   ├── WithCountryStep.php
            │   │   ├── Wizardable.php
            │   ├── Allowable/
            │   │   ├── DeveloperAllowedInterface.php
            │   ├── AllowableInterface.php
            │   ├── AllowableMigrationInterface.php
            │   ├── ChangelogHandlerInterface.php
            │   ├── ChangelogViaHandlerInterface.php
            │   ├── ChangelogableInterface.php
            │   ├── FilterCriteriaInterface.php
            │   ├── Formable.php
            │   ├── HiddenVariablesInterface.php
            │   ├── Iconizable.php
            │   ├── Instanceable.php
            │   ├── Lockable.php
            │   ├── LockableItem.php
            │   ├── LockableRecord.php
            │   ├── Loggable.php
            │   ├── Propertizable.php
            │   ├── Simulatable.php
            ├── LDAP/
            │   ├── Config.php
            │   ├── LDAP.php
            │   ├── LDAPException.php
            ├── Languages/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── UITranslationDevMode.php
            │   ├── Language.php
            │   ├── LanguageException.php
            │   ├── Languages.php
            ├── Locales/
            │   ├── API/
            │   │   ├── AppLocaleAPIInterface.php
            │   │   ├── AppLocaleAPIResponseInterface.php
            │   │   ├── AppLocaleAPITrait.php
            │   │   ├── AppLocaleParam.php
            │   │   ├── LocalesAPIGroup.php
            │   │   ├── Methods/
            │   │   │   └── GetAppLocalesAPI.php
            │   ├── Locale.php
            │   ├── Locales.php
            ├── Localization/
            │   ├── Localization.php
            ├── LockManager/
            │   ├── AjaxMethod.php
            │   ├── Lock.php
            │   ├── LockManager.php
            │   ├── LockingFilterCriteria.php
            │   ├── LockingFilterSettings.php
            ├── Logger/
            │   ├── Logger.php
            │   ├── PSRLogger.php
            ├── LookupItems/
            │   ├── BaseDBCollectionLookupItem.php
            │   ├── BaseLookupItem.php
            │   ├── BaseRevisionableLookupItem.php
            │   ├── Item.php
            │   ├── LookupItems.php
            │   ├── Result.php
            ├── Mail/
            │   ├── Mailer.php
            ├── Maintenance/
            │   ├── Admin/
            │   │   ├── MaintenanceScreenRights.php
            │   │   ├── Screens/
            │   │   │   ├── CreateSubmode.php
            │   │   │   ├── ListSubmode.php
            │   │   │   ├── MaintenanceMode.php
            │   │   ├── Traits/
            │   │   │   └── MaintenanceSubmodeInterface.php
            │   │   │   └── MaintenanceSubmodeTrait.php
            │   ├── Maintenance.php
            │   ├── Plan.php
            ├── MarkdownRenderer/
            │   ├── BaseCustomTag.php
            │   ├── CustomTags/
            │   │   ├── APIMethodDocTag.php
            │   │   ├── MediaTag.php
            │   ├── MarkdownRenderer.php
            │   ├── README.md
            │   ├── docs/
            │   │   ├── custom-tags.md
            │   │   ├── public-api.md
            │   ├── module-context.yaml
            ├── Media/
            │   ├── Admin/
            │   │   ├── MediaAdminURLs.php
            │   │   ├── MediaRecordAdminURLs.php
            │   │   ├── MediaScreenRights.php
            │   │   ├── Screens/
            │   │   │   ├── MediaLibraryArea.php
            │   │   │   ├── Mode/
            │   │   │   │   └── CreateMode.php
            │   │   │   │   └── GlobalSettingsMode.php
            │   │   │   │   └── ImageGalleryMode.php
            │   │   │   │   └── ListMode.php
            │   │   │   │   └── View/
            │   │   │   │       ├── SettingsSubmode.php
            │   │   │   │       ├── StatusSubmode.php
            │   │   │   │       ├── TagsSubmode.php
            │   │   │   │   └── ViewMode.php
            │   │   ├── Traits/
            │   │   │   └── MediaModeInterface.php
            │   │   │   └── MediaModeTrait.php
            │   │   │   └── MediaViewInterface.php
            │   │   │   └── MediaViewTrait.php
            │   ├── Collection/
            │   │   ├── MediaCollection.php
            │   │   ├── MediaFilterCriteria.php
            │   │   ├── MediaFilterSettings.php
            │   │   ├── MediaRecord.php
            │   │   ├── MediaSettingsManager.php
            │   ├── Configuration.php
            │   ├── Configuration/
            │   │   ├── Image.php
            │   ├── Container.php
            │   ├── Delivery.php
            │   ├── Document.php
            │   ├── Document/
            │   │   ├── Image.php
            │   │   ├── PDF.php
            │   ├── DocumentInterface.php
            │   ├── DocumentTrait.php
            │   ├── Events/
            │   │   ├── RegisterMediaTagsListener.php
            │   ├── ImageDocumentInterface.php
            │   ├── ImageDocumentTrait.php
            │   ├── Media.php
            │   ├── MediaException.php
            │   ├── MediaRightsInterface.php
            │   ├── MediaRightsTrait.php
            │   ├── MediaTagConnector.php
            │   ├── Processor.php
            │   ├── ThumbnailRenderer.php
            ├── Messagelogs/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   └── MessageLogDevelMode.php
            │   ├── FilterCriteria.php
            │   ├── FilterSettings.php
            │   ├── Log.php
            │   ├── Messagelogs.php
            ├── Messaging/
            │   ├── Message.php
            │   ├── MessagingCollection.php
            │   ├── MessagingException.php
            │   ├── MessagingFilterCriteria.php
            │   ├── MessagingFilterSettings.php
            ├── NewsCentral/
            │   ├── Admin/
            │   │   ├── CategoriesAdminURLs.php
            │   │   ├── CategoryAdminURLs.php
            │   │   ├── ManageNewsAdminURLs.php
            │   │   ├── NewsAdminURLs.php
            │   │   ├── NewsEntryAdminURLs.php
            │   │   ├── NewsScreenRights.php
            │   │   ├── NewsScreens.php
            │   │   ├── ReadNewsAdminURLs.php
            │   │   ├── Screens/
            │   │   │   ├── ManageNews/
            │   │   │   │   ├── CategoriesListMode.php
            │   │   │   │   ├── CreateAlertScreen.php
            │   │   │   │   ├── CreateArticleScreen.php
            │   │   │   │   ├── CreateCategoryMode.php
            │   │   │   │   ├── NewsListMode.php
            │   │   │   │   ├── ViewArticle/
            │   │   │   │   │   ├── ArticleSettingsSubmode.php
            │   │   │   │   │   ├── ArticleStatusSubmode.php
            │   │   │   │   ├── ViewArticleMode.php
            │   │   │   │   ├── ViewCategory/
            │   │   │   │   │   ├── CategorySettingsSubmode.php
            │   │   │   │   ├── ViewCategoryMode.php
            │   │   │   ├── ManageNewsArea.php
            │   │   │   ├── ReadNews/
            │   │   │   │   ├── ArticlesListMode.php
            │   │   │   │   ├── ReadArticleScreen.php
            │   │   │   ├── ReadNewsArea.php
            │   │   ├── Traits/
            │   │   │   └── ManageNewsModeInterface.php
            │   │   │   └── ManageNewsModeTrait.php
            │   │   │   └── ReadNewsModeInterface.php
            │   │   │   └── ReadNewsModeTrait.php
            │   │   │   └── ViewArticleSubmodeInterface.php
            │   │   │   └── ViewArticleSubmodeTrait.php
            │   ├── Categories/
            │   │   ├── CategoriesCollection.php
            │   │   ├── CategoriesFilterCriteria.php
            │   │   ├── CategoriesFilterSettings.php
            │   │   ├── Category.php
            │   │   ├── CategorySelector.php
            │   │   ├── CategorySettingsManager.php
            │   ├── Entries/
            │   │   ├── Criticalities/
            │   │   │   ├── NewsEntryCriticalities.php
            │   │   │   ├── NewsEntryCriticality.php
            │   │   ├── EntryCategoriesManager.php
            │   │   ├── NewsAlert.php
            │   │   ├── NewsArticle.php
            │   │   ├── NewsEntry.php
            │   │   ├── Statuses/
            │   │   │   ├── NewsEntryStatus.php
            │   │   │   ├── NewsEntryStatuses.php
            │   │   ├── Types/
            │   │   │   └── NewsEntryType.php
            │   │   │   └── NewsEntryTypes.php
            │   ├── NewsCollection.php
            │   ├── NewsFilterCriteria.php
            │   ├── NewsFilterSettings.php
            │   ├── NewsSettingsManager.php
            │   ├── User/
            │   │   └── NewsRightsInterface.php
            │   │   └── NewsRightsTrait.php
            ├── OAuth/
            │   ├── Config.php
            │   ├── Exception.php
            │   ├── OAuth.php
            │   ├── Strategy.php
            │   ├── Strategy/
            │   │   └── Facebook.php
            │   │   └── GitHub.php
            │   │   └── Google.php
            ├── Ratings/
            │   ├── FilterCriteria.php
            │   ├── FilterSettings.php
            │   ├── Rating.php
            │   ├── Ratings.php
            │   ├── Screens/
            │   │   └── RatingScreenRecord.php
            │   │   └── RatingScreensCollection.php
            │   │   └── RatingScreensFilterCriteria.php
            │   │   └── RatingScreensFilterSettings.php
            ├── Renamer/
            │   ├── Admin/
            │   │   ├── RenamerAdminURLs.php
            │   │   ├── RenamerScreenRights.php
            │   │   ├── Screens/
            │   │   │   ├── Mode/
            │   │   │   │   ├── RenamerMode.php
            │   │   │   ├── Submode/
            │   │   │   │   └── ConfigurationSubmode.php
            │   │   │   │   └── ExportSubmode.php
            │   │   │   │   └── ReplaceSubmode.php
            │   │   │   │   └── ResultsSubmode.php
            │   │   │   │   └── SearchSubmode.php
            │   │   ├── Traits/
            │   │   │   └── RenamerSubmodeInterface.php
            │   │   │   └── RenamerSubmodeTrait.php
            │   ├── BaseDataColumn.php
            │   ├── DataColumnInterface.php
            │   ├── DataColumnsCollection.php
            │   ├── Index/
            │   │   ├── RenamerFilterCriteria.php
            │   │   ├── RenamerFilterSettings.php
            │   │   ├── RenamerIndex.php
            │   │   ├── RenamerIndexRunner.php
            │   │   ├── RenamerRecord.php
            │   ├── RenamerConfig.php
            │   ├── RenamerException.php
            │   ├── RenamerSettingsManager.php
            │   ├── RenamingManager.php
            ├── Request/
            │   ├── Request.php
            ├── RequestLog/
            │   ├── AbstractFileContainer.php
            │   ├── AbstractFolderContainer.php
            │   ├── AbstractLogContainer.php
            │   ├── AbstractLogItem.php
            │   ├── EnabledStatus.php
            │   ├── Exception.php
            │   ├── FileFilterCriteria.php
            │   ├── FileFilterCriteria/
            │   │   ├── FileMatcher.php
            │   │   ├── FileMatcher/
            │   │   │   └── DurationSearch.php
            │   │   │   └── StringSearch.php
            │   ├── FileFilterSettings.php
            │   ├── LogInfo.php
            │   ├── LogItemInterface.php
            │   ├── LogItems/
            │   │   ├── Day.php
            │   │   ├── Hour.php
            │   │   ├── LogFile.php
            │   │   ├── Month.php
            │   │   ├── Year.php
            │   ├── LogWriter.php
            │   ├── RequestLog.php
            ├── Revisionable/
            │   ├── Admin/
            │   │   ├── RequestTypes/
            │   │   │   ├── RevisionableCollectionScreenInterface.php
            │   │   │   ├── RevisionableScreenInterface.php
            │   │   │   ├── RevisionableScreenTrait.php
            │   │   ├── Screens/
            │   │   │   ├── Action/
            │   │   │   │   ├── BaseRevisionableChangelogAction.php
            │   │   │   │   ├── BaseRevisionableRecordAction.php
            │   │   │   │   ├── BaseRevisionableSettingsAction.php
            │   │   │   │   ├── BaseRevisionableStatusAction.php
            │   │   │   ├── Mode/
            │   │   │   │   ├── BaseRevisionableChangelogMode.php
            │   │   │   │   ├── BaseRevisionableListMode.php
            │   │   │   ├── Submode/
            │   │   │   │   └── BaseRevisionableChangelogSubmode.php
            │   │   │   │   └── BaseRevisionableListSubmode.php
            │   │   │   │   └── BaseRevisionableRecordSubmode.php
            │   │   │   │   └── BaseRevisionableSettingsSubmode.php
            │   │   ├── Traits/
            │   │   │   └── RevisionableChangelogScreenInterface.php
            │   │   │   └── RevisionableChangelogScreenTrait.php
            │   │   │   └── RevisionableListScreenInterface.php
            │   │   │   └── RevisionableListScreenTrait.php
            │   │   │   └── RevisionableSettingsScreenInterface.php
            │   │   │   └── RevisionableSettingsScreenTrait.php
            │   ├── BaseRevisionable.php
            │   ├── Changelog/
            │   │   ├── BaseRevisionableChangelogHandler.php
            │   │   ├── RevisionableChangelogHandlerInterface.php
            │   │   ├── RevisionableChangelogInterface.php
            │   │   ├── RevisionableChangelogTrait.php
            │   ├── Collection/
            │   │   ├── AjaxMethod.php
            │   │   ├── BaseRevisionableCollection.php
            │   │   ├── BaseRevisionableDataGridMultiAction.php
            │   │   ├── BaseRevisionableFilterCriteria.php
            │   │   ├── BaseRevisionableFilterSettings.php
            │   │   ├── FilterSettings/
            │   │   │   ├── StateFilter.php
            │   │   ├── RevisionCopy.php
            │   │   ├── RevisionableCollectionFilteringInterface.php
            │   │   ├── RevisionableCollectionInterface.php
            │   │   ├── RevisionableFilterCriteriaInterface.php
            │   │   ├── RevisionableFilterSettingsInterface.php
            │   │   ├── RevisionableStateFilterTrait.php
            │   ├── Event/
            │   │   ├── BeforeSaveEvent.php
            │   │   ├── RevisionAddedEvent.php
            │   │   ├── RevisionSelectedEvent.php
            │   │   ├── TransactionEndedEvent.php
            │   ├── RevisionDependentInterface.php
            │   ├── RevisionableException.php
            │   ├── RevisionableInterface.php
            │   ├── StatusHandling/
            │   │   ├── StandardStateSetupFilterInterface.php
            │   │   ├── StandardStateSetupFilterTrait.php
            │   │   ├── StandardStateSetupInterface.php
            │   │   ├── StandardStateSetupTrait.php
            │   ├── Storage/
            │   │   ├── BaseDBCollectionStorage.php
            │   │   ├── BaseDBRevisionStorage.php
            │   │   ├── BaseDBStandardizedStorage.php
            │   │   ├── BaseRevisionStorage.php
            │   │   ├── Copy/
            │   │   │   ├── BaseDBRevisionCopy.php
            │   │   │   ├── BaseRevisionCopy.php
            │   │   ├── Event/
            │   │   │   ├── StorageRevisionAddedEvent.php
            │   │   │   ├── StorageRevisionSelectedEvent.php
            │   │   ├── RevisionStorageException.php
            │   │   ├── RevisionableStorageException.php
            │   │   ├── StubDBRevisionStorage.php
            │   ├── TransactionInfo.php
            ├── Session/
            │   ├── AuthTypes/
            │   │   ├── CAS.php
            │   │   ├── CASInterface.php
            │   │   ├── None.php
            │   │   ├── NoneInterface.php
            │   │   ├── OAuth.php
            │   │   ├── OAuthInterface.php
            │   ├── Base.php
            │   ├── Events/
            │   │   ├── BaseSessionInstantiatedListener.php
            │   │   ├── BeforeLogOutEvent.php
            │   │   ├── SessionInstantiatedEvent.php
            │   │   ├── SessionStartedEvent.php
            │   │   ├── UserAuthenticatedEvent.php
            │   ├── Exception.php
            │   ├── Native.php
            │   ├── NativeCASSession.php
            │   ├── Session.php
            ├── SourceFolders/
            │   ├── BaseSourceFolder.php
            │   ├── KnownSources.php
            │   ├── SourceFoldersManager.php
            │   ├── Sources/
            │   │   └── APISourceFolders.php
            │   │   └── AjaxSourceFolders.php
            │   │   └── DeploymentTaskFolders.php
            │   │   └── FormElementFolders.php
            ├── StateHandler/
            │   ├── State.php
            │   ├── StateHandler.php
            │   ├── StateHandlerException.php
            ├── Stubs/
            │   ├── Formable/
            │   │   └── RecordSettingsExtendedStub.php
            │   │   └── RecordSettingsStub.php
            ├── SystemMails/
            │   ├── MailContents/
            │   │   ├── BaseMailContent.php
            │   │   ├── MailButton.php
            │   │   ├── MailParagraph.php
            │   │   ├── MailPreformatted.php
            │   │   ├── MailSeparator.php
            │   ├── SystemMail.php
            │   ├── SystemMailException.php
            │   ├── SystemMailer.php
            ├── Tags/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   ├── Area/
            │   │   │   │   ├── TagsArea.php
            │   │   │   ├── Mode/
            │   │   │   │   └── CreateMode.php
            │   │   │   │   └── ListMode.php
            │   │   │   │   └── View/
            │   │   │   │       ├── SettingsSubmode.php
            │   │   │   │       ├── TagTreeSubmode.php
            │   │   │   │   └── ViewMode.php
            │   │   ├── TagScreenRights.php
            │   │   ├── Traits/
            │   │   │   └── RecordTaggingScreenInterface.php
            │   │   │   └── RecordTaggingScreenTrait.php
            │   │   │   └── TagModeInterface.php
            │   │   │   └── TagModeTrait.php
            │   │   │   └── ViewSubmodeInterface.php
            │   │   │   └── ViewSubmodeTrait.php
            │   ├── Ajax/
            │   │   ├── TaggableAjaxRequestInterface.php
            │   │   ├── TaggableAjaxRequestTrait.php
            │   ├── Events/
            │   │   ├── BaseRegisterTagCollectionsListener.php
            │   │   ├── RegisterTagCollectionsEvent.php
            │   ├── Interfaces/
            │   │   ├── TagFilterSettingsInterface.php
            │   ├── TagCollection.php
            │   ├── TagCollectionRegistry.php
            │   ├── TagCriteria.php
            │   ├── TagException.php
            │   ├── TagFilterSettings.php
            │   ├── TagRecord.php
            │   ├── TagRegistry.php
            │   ├── TagSettingsManager.php
            │   ├── TagSortType.php
            │   ├── TagSortTypes.php
            │   ├── Taggables/
            │   │   ├── FilterCriteria/
            │   │   │   ├── TaggableFilterCriteriaInterface.php
            │   │   │   ├── TaggableFilterCriteriaTrait.php
            │   │   ├── TagCollectionInterface.php
            │   │   ├── TagCollectionTrait.php
            │   │   ├── TagConnector.php
            │   │   ├── TagEditorUI.php
            │   │   ├── Taggable.php
            │   │   ├── TaggableInterface.php
            │   │   ├── TaggableTrait.php
            │   │   ├── TaggableUniqueID.php
            │   ├── TaggingException.php
            │   ├── TagsRightsInterface.php
            │   ├── TagsRightsTrait.php
            │   ├── Traits/
            │   │   └── TagFilterSettingsTrait.php
            ├── TimeTracker/
            │   ├── Admin/
            │   │   ├── EntryAdminURLs.php
            │   │   ├── ListBuilder/
            │   │   │   ├── SummarizedTicket.php
            │   │   │   ├── TicketSummaryRenderer.php
            │   │   ├── Screens/
            │   │   │   ├── Mode/
            │   │   │   │   ├── AutoFillMode.php
            │   │   │   │   ├── CreateEntryMode.php
            │   │   │   │   ├── CreateTimeSpanMode.php
            │   │   │   │   ├── ExportMode.php
            │   │   │   │   ├── ImportMode.php
            │   │   │   │   ├── List/
            │   │   │   │   │   ├── DayListSubmode.php
            │   │   │   │   │   ├── GlobalListSubmode.php
            │   │   │   │   │   ├── GlobalSettingsSubmode.php
            │   │   │   │   │   ├── TimeSpanListSubmode.php
            │   │   │   │   ├── ListMode.php
            │   │   │   │   ├── View/
            │   │   │   │   │   ├── SettingsSubmode.php
            │   │   │   │   │   ├── StatusSubmode.php
            │   │   │   │   ├── ViewMode.php
            │   │   │   ├── TimeTrackerArea.php
            │   │   ├── TimeListBuilder.php
            │   │   ├── TimeSpansAdminURLs.php
            │   │   ├── TimeTrackerScreenRights.php
            │   │   ├── TimeUIManager.php
            │   │   ├── TrackerAdminURLs.php
            │   │   ├── Traits/
            │   │   │   └── ListSubmodeInterface.php
            │   │   │   └── ListSubmodeTrait.php
            │   │   │   └── ModeInterface.php
            │   │   │   └── ModeTrait.php
            │   │   │   └── ViewSubmodeInterface.php
            │   │   │   └── ViewSubmodeTrait.php
            │   ├── AutoFiller/
            │   │   ├── WorkBlock.php
            │   ├── Export/
            │   │   ├── TimeExportException.php
            │   │   ├── TimeExporter.php
            │   │   ├── TimeImporter.php
            │   ├── TimeEntry.php
            │   ├── TimeFilterCriteria.php
            │   ├── TimeFilterSettings.php
            │   ├── TimeSettingsManager.php
            │   ├── TimeSpans/
            │   │   ├── SidebarSpans.php
            │   │   ├── SpanTypes/
            │   │   │   ├── BaseTimeSpanType.php
            │   │   │   ├── TimeSpanTypeInterface.php
            │   │   │   ├── TimeSpanTypeSelector.php
            │   │   │   ├── TimeSpanTypes.php
            │   │   │   ├── Type/
            │   │   │   │   └── HolidayTimeSpan.php
            │   │   │   │   └── SickLeaveTimeSpan.php
            │   │   │   │   └── VacationTimeSpan.php
            │   │   ├── TimeSpanCollection.php
            │   │   ├── TimeSpanException.php
            │   │   ├── TimeSpanFilterCriteria.php
            │   │   ├── TimeSpanFilterSettings.php
            │   │   ├── TimeSpanRecord.php
            │   │   ├── TimeSpanSettingsManager.php
            │   ├── TimeTrackerCollection.php
            │   ├── TimeTrackerException.php
            │   ├── Types/
            │   │   ├── TimeEntryType.php
            │   │   ├── TimeEntryTypes.php
            │   ├── User/
            │   │   └── TimeTrackerRightsInterface.php
            │   │   └── TimeTrackerRightsTrait.php
            ├── Traits/
            │   ├── Admin/
            │   │   ├── RequestTypes/
            │   │   │   ├── RequestCountryTrait.php
            │   │   ├── Screen.php
            │   │   ├── ScreenAccessTrait.php
            │   │   ├── ScreenDisplayMode.php
            │   │   ├── Wizard.php
            │   │   ├── Wizard/
            │   │   │   └── Step/
            │   │   │       ├── Confirmation.php
            │   │   │       ├── CreateDBRecordStep.php
            │   │   │       ├── SelectCountryStep.php
            │   │   │       ├── SettingsManagerStep.php
            │   │   │   └── WithConfirmationStep.php
            │   │   │   └── WithCountryStep.php
            │   ├── Allowable/
            │   │   ├── DeveloperAllowedTrait.php
            │   ├── AllowableMigrationTrait.php
            │   ├── ChangelogViaHandlerTrait.php
            │   ├── HiddenVariablesTrait.php
            │   ├── Iconizable.php
            │   ├── Instanceable.php
            │   ├── LockableItem.php
            │   ├── LockableStatus.php
            │   ├── LockableWithManager.php
            │   ├── Loggable.php
            │   ├── Propertizable.php
            │   ├── RevisionDependentTrait.php
            │   ├── Simulatable.php
            │   ├── Stubs/
            │   │   └── Admin/
            │   │       └── Wizard/
            │   │           └── Step/
            │   │               ├── ConfirmationStub.php
            │   │               ├── CreateDBRecordStub.php
            │   │               ├── SelectCountryStub.php
            │   │               ├── SettingsManagerStub.php
            │   │           └── WithConfirmationStepStub.php
            │   │           └── WithCountryStepStub.php
            ├── Updaters/
            │   ├── BaseStepBasedUpdater.php
            │   ├── BaseUpdater.php
            │   ├── Bundled/
            │   │   ├── EmailHashUpdater.php
            │   ├── UpdaterInterface.php
            │   ├── UpdatersCollection.php
            ├── Uploads/
            │   ├── LocalFileUpload.php
            │   ├── Upload.php
            │   ├── Uploads.php
            ├── User/
            │   ├── Extended.php
            │   ├── Interface.php
            │   ├── LayoutWidth.php
            │   ├── LayoutWidths.php
            │   ├── Notepad.php
            │   ├── Notepad/
            │   │   ├── Note.php
            │   ├── Recent.php
            │   ├── Recent/
            │   │   ├── Category.php
            │   │   ├── Entry.php
            │   │   ├── NoteCategory.php
            │   ├── Rights.php
            │   ├── Rights/
            │   │   ├── Container.php
            │   │   ├── Group.php
            │   │   ├── Right.php
            │   │   ├── Role.php
            │   ├── Role/
            │   │   ├── DeveloperRole.php
            │   │   ├── NewsEditorRole.php
            │   ├── Roles/
            │   │   ├── BaseRole.php
            │   │   ├── RoleCollection.php
            │   ├── ScreenTracker.php
            │   ├── Statistics.php
            │   ├── Storage.php
            │   ├── Storage/
            │   │   ├── DB.php
            │   │   ├── File.php
            │   ├── User.php
            │   ├── UserException.php
            ├── Users/
            │   ├── Admin/
            │   │   ├── Screens/
            │   │   │   ├── Manage/
            │   │   │   │   ├── ManageUsersArea.php
            │   │   │   │   ├── Mode/
            │   │   │   │   │   └── CreateMode.php
            │   │   │   │   │   └── ListMode.php
            │   │   │   │   │   └── ViewMode.php
            │   │   │   ├── RightsOverviewDevelMode.php
            │   │   │   ├── UserSettingsArea.php
            │   │   ├── Traits/
            │   │   │   ├── ManageModeInterface.php
            │   │   │   ├── ManageModeTrait.php
            │   │   │   ├── ViewSubmodeInterface.php
            │   │   │   ├── ViewSubmodeTrait.php
            │   │   ├── UserAdminScreenRights.php
            │   │   ├── UserAdminURLs.php
            │   │   ├── UsersAdminURLs.php
            │   ├── Rights/
            │   │   ├── UserAdminRightsInterface.php
            │   │   ├── UserAdminRightsTrait.php
            │   ├── User.php
            │   ├── UserSelector.php
            │   ├── Users.php
            │   ├── UsersException.php
            │   ├── UsersFilterCriteria.php
            │   ├── UsersFilterSettings.php
            │   ├── UsersSettingsManager.php
            ├── Validation/
            │   ├── ValidatableInterface.php
            │   ├── ValidatableTrait.php
            │   ├── ValidationLoggableInterface.php
            │   ├── ValidationLoggableTrait.php
            │   ├── ValidationResult.php
            │   ├── ValidationResultInterface.php
            │   ├── ValidationResults.php
            ├── WhatsNew/
            │   └── Admin/
            │       ├── Screens/
            │       │   ├── CreateSubmode.php
            │       │   ├── EditSubmode.php
            │       │   ├── ListSubmode.php
            │       │   ├── WhatsNewEditorMode.php
            │       ├── Traits/
            │       │   ├── WhatsNewSubmodeInterface.php
            │       │   ├── WhatsNewSubmodeTrait.php
            │       ├── WhatsNewScreenRights.php
            │   └── AppVersion.php
            │   └── AppVersion/
            │       ├── CategoryItem.php
            │       ├── LanguageCategory.php
            │       ├── LinkedImage.php
            │       ├── VersionLanguage.php
            │       ├── VersionLanguage/
            │       │   └── DE.php
            │       │   └── DEV.php
            │       │   └── EN.php
            │   └── PlainTextRenderer.php
            │   └── WhatsNew.php
            │   └── WhatsNewException.php
            │   └── WhatsNewImage.php
            │   └── XMLFileWriter.php
            │   └── XMLRenderer.php
        └── Connectors/
            ├── Connector/
            │   ├── BaseConnector.php
            │   ├── BaseConnectorMethod.php
            │   ├── ConnectorException.php
            │   ├── ConnectorInterface.php
            │   ├── Method/
            │   │   ├── Delete.php
            │   │   ├── Get.php
            │   │   ├── Post.php
            │   │   ├── Put.php
            │   ├── Stub/
            │   │   ├── Method/
            │   │   │   └── StubFailureMethod.php
            │   ├── StubConnector.php
            ├── Connectors.php
            ├── ConnectorsException.php
            ├── Headers/
            │   ├── HTTPHeader.php
            │   ├── HTTPHeadersBasket.php
            ├── ProxyConfiguration.php
            ├── README.md
            ├── Request.php
            ├── Request/
            │   ├── Cache.php
            │   ├── Method.php
            │   ├── RequestSerializer.php
            │   ├── URL.php
            ├── Response.php
            ├── Response/
            │   ├── ResponseEndpointError.php
            │   ├── ResponseError.php
            │   ├── ResponseSerializer.php
            ├── ResponseCode.php
            ├── module-context.yaml
        └── DBHelper/
            ├── API/
            │   ├── Methods/
            │   │   └── DescribeCollectionsAPI.php
            ├── Admin/
            │   ├── BaseCollectionListBuilder.php
            │   ├── BaseDBRecordSelectionTieIn.php
            │   ├── DBHelperAdminException.php
            │   ├── DBRecordSelectionTieInInterface.php
            │   ├── Requests/
            │   │   ├── BaseDBRecordRequestType.php
            │   ├── Screens/
            │   │   ├── Action/
            │   │   │   ├── BaseRecordAction.php
            │   │   │   ├── BaseRecordCreateAction.php
            │   │   │   ├── BaseRecordDeleteAction.php
            │   │   │   ├── BaseRecordListAction.php
            │   │   │   ├── BaseRecordSettingsAction.php
            │   │   │   ├── BaseRecordStatusAction.php
            │   │   ├── Mode/
            │   │   │   ├── BaseRecordCreateMode.php
            │   │   │   ├── BaseRecordListMode.php
            │   │   │   ├── BaseRecordMode.php
            │   │   ├── Submode/
            │   │   │   └── BaseRecordCreateSubmode.php
            │   │   │   └── BaseRecordDeleteSubmode.php
            │   │   │   └── BaseRecordListSubmode.php
            │   │   │   └── BaseRecordSettingsSubmode.php
            │   │   │   └── BaseRecordStatusSubmode.php
            │   │   │   └── BaseRecordSubmode.php
            │   ├── Traits/
            │   │   └── RecordCreateScreenInterface.php
            │   │   └── RecordCreateScreenTrait.php
            │   │   └── RecordDeleteScreenInterface.php
            │   │   └── RecordDeleteScreenTrait.php
            │   │   └── RecordEditScreenInterface.php
            │   │   └── RecordEditScreenTrait.php
            │   │   └── RecordListScreenInterface.php
            │   │   └── RecordListScreenTrait.php
            │   │   └── RecordScreenInterface.php
            │   │   └── RecordScreenTrait.php
            │   │   └── RecordSettingsScreenInterface.php
            │   │   └── RecordSettingsScreenTrait.php
            │   │   └── RecordStatusScreenInterface.php
            │   │   └── RecordStatusScreenTrait.php
            ├── Attributes/
            │   ├── UncachedQuery.php
            ├── BaseCollection.php
            ├── BaseCollection/
            │   ├── BaseChildCollection.php
            │   ├── ChildCollectionInterface.php
            │   ├── DBHelperCollectionException.php
            │   ├── DBHelperCollectionInterface.php
            │   ├── Event/
            │   │   ├── AfterCreateRecordEvent.php
            │   │   ├── AfterDeleteRecordEvent.php
            │   │   ├── BeforeCreateRecordEvent.php
            │   ├── Keys.php
            │   ├── Keys/
            │   │   ├── Key.php
            │   ├── OperationContext.php
            │   ├── OperationContext/
            │   │   └── Create.php
            │   │   └── Delete.php
            │   │   └── Save.php
            ├── BaseFilterCriteria.php
            ├── BaseFilterCriteria/
            │   ├── BaseCollectionFilteringInterface.php
            │   ├── IntegerCollectionFilteringInterface.php
            │   ├── Record.php
            │   ├── StringCollectionFilteringInterface.php
            ├── BaseFilterSettings.php
            ├── BaseRecord.php
            ├── BaseRecord/
            │   ├── BaseRecordDecorator.php
            │   ├── BaseRecordException.php
            │   ├── Event/
            │   │   └── KeyModifiedEvent.php
            ├── BaseRecordSettings.php
            ├── CaseStatement.php
            ├── DBHelper.php
            ├── DBHelperFilterCriteriaInterface.php
            ├── DBHelperFilterSettingsInterface.php
            ├── DataTable.php
            ├── DataTable/
            │   ├── Events/
            │   │   └── KeysDeleted.php
            │   │   └── KeysSaved.php
            ├── Docs/
            │   ├── dbhelper-database-abstraction.md
            │   ├── dbhelper-record-collections.md
            ├── Event.php
            ├── Exception.php
            ├── Exception/
            │   ├── BaseErrorRenderer.php
            │   ├── CLIErrorRenderer.php
            │   ├── HTMLErrorRenderer.php
            ├── FetchBase.php
            ├── FetchKey.php
            ├── FetchMany.php
            ├── FetchOne.php
            ├── Interfaces/
            │   ├── DBHelperRecordInterface.php
            ├── OperationTypes.php
            ├── README.md
            ├── StatementBuilder.php
            ├── StatementBuilder/
            │   ├── ValueDefinition.php
            │   ├── ValuesContainer.php
            ├── TrackedQuery.php
            ├── Traits/
            │   ├── AfterRecordCreatedEventTrait.php
            │   ├── BeforeCreateEventTrait.php
            │   ├── LooseDBRecordInterface.php
            │   ├── LooseDBRecordTrait.php
            │   ├── RecordDecoratorInterface.php
            │   ├── RecordDecoratorTrait.php
            │   ├── RecordKeyHandlersTrait.php
            ├── module-context.yaml
        └── DeeplHelper/
            ├── Admin/
            │   ├── DeeplAdminURLs.php
            │   ├── DeeplScreenRights.php
            │   ├── Screens/
            │   │   └── DeepLTestScreen.php
            ├── DeeplHelper.php
            ├── DeeplHelperException.php
        └── Examples/
            ├── Herbs/
            │   ├── HerbRecord.php
            │   ├── HerbsCollection.php
            ├── InterfaceExamples.php
            ├── UserInterface/
            │   └── ExampleFile.php
            │   └── ExamplesCategory.php
        └── TypeHinter/
            ├── TypeHintRunner.php
            ├── UpdateV1_21.php
        └── UI/
            ├── Admin/
            │   ├── Screens/
            │   │   └── AppInterfaceDevelMode.php
            │   │   └── CSSGenDevelMode.php
            ├── AdminURLs/
            │   ├── AdminURL.php
            │   ├── AdminURLException.php
            │   ├── AdminURLInterface.php
            │   ├── AdminURLsInterface.php
            │   ├── README.md
            │   ├── module-context.yaml
            ├── Badge.php
            ├── BaseLockable.php
            ├── BaseUIEvent.php
            ├── Bootstrap.php
            ├── Bootstrap/
            │   ├── Anchor.php
            │   ├── BadgeDropdown.php
            │   ├── BaseDropdown.php
            │   ├── BigSelection/
            │   │   ├── BaseItem.php
            │   │   ├── BigSelectionCSS.php
            │   │   ├── BigSelectionWidget.php
            │   │   ├── Item/
            │   │   │   └── HeaderItem.php
            │   │   │   └── RegularItem.php
            │   │   │   └── SeparatorItem.php
            │   ├── ButtonDropdown.php
            │   ├── ButtonGroup.php
            │   ├── ButtonGroup/
            │   │   ├── ButtonGroupItemInterface.php
            │   ├── Dropdown/
            │   │   ├── AJAXLoader.php
            │   ├── DropdownAnchor.php
            │   ├── DropdownDivider.php
            │   ├── DropdownHeader.php
            │   ├── DropdownMenu.php
            │   ├── DropdownStatic.php
            │   ├── DropdownSubmenu.php
            │   ├── Popover.php
            │   ├── README.md
            │   ├── Tab.php
            │   ├── Tab/
            │   │   ├── Renderer.php
            │   │   ├── Renderer/
            │   │   │   └── Link.php
            │   │   │   └── Menu.php
            │   │   │   └── Toggle.php
            │   ├── Tabs.php
            │   ├── module-context.yaml
            ├── Button.php
            ├── CSSClasses.php
            ├── CSSGenerator/
            │   ├── CSSGen.php
            │   ├── CSSGenException.php
            │   ├── CSSGenFile.php
            │   ├── CSSGenLocation.php
            ├── ClientConfirmable/
            │   ├── Message.php
            ├── ClientResource.php
            ├── ClientResource/
            │   ├── Javascript.php
            │   ├── README.md
            │   ├── Stylesheet.php
            │   ├── module-context.yaml
            ├── ClientResourceCollection.php
            ├── CriticalityEnum.php
            ├── DataGrid.php
            ├── DataGrid/
            │   ├── Action.php
            │   ├── Action/
            │   │   ├── Confirm.php
            │   │   ├── Default.php
            │   │   ├── Javascript.php
            │   │   ├── Separator.php
            │   ├── BaseListBuilder.php
            │   ├── Column.php
            │   ├── Column/
            │   │   ├── ColumnSettingStorage.php
            │   │   ├── MultiSelect.php
            │   ├── Entry.php
            │   ├── Entry/
            │   │   ├── Heading.php
            │   │   ├── Merged.php
            │   ├── EntryClientCommands.php
            │   ├── Exception.php
            │   ├── GridClientCommands.php
            │   ├── GridConfigurator.php
            │   ├── ListBuilder/
            │   │   ├── ListBuilderScreenInterface.php
            │   │   ├── ListBuilderScreenTrait.php
            │   ├── README.md
            │   ├── RedirectMessage.php
            │   ├── Row.php
            │   ├── Row/
            │   │   ├── Sums.php
            │   │   ├── Sums/
            │   │   │   └── ColumnDef.php
            │   │   │   └── ColumnDef/
            │   │   │       └── Callback.php
            │   ├── module-context.yaml
            ├── Docs/
            │   ├── themes-and-templates.md
            │   ├── ui-helper-classes.md
            ├── Event/
            │   ├── FormCreatedEvent.php
            │   ├── PageRendered.php
            ├── Exception.php
            ├── Form.php
            ├── Form/
            │   ├── Element/
            │   │   ├── DateTimePicker/
            │   │   │   ├── BasicTime.php
            │   │   ├── Datepicker.php
            │   │   ├── ExpandableSelect.php
            │   │   ├── HTMLDatePicker.php
            │   │   ├── HTMLDateTimePicker.php
            │   │   ├── HTMLTimePicker.php
            │   │   ├── ImageUploader.php
            │   │   ├── Multiselect.php
            │   │   ├── Switch.php
            │   │   ├── TreeSelect.php
            │   │   ├── UIButton.php
            │   │   ├── VisualSelect.php
            │   │   ├── VisualSelect/
            │   │   │   └── ImageSet.php
            │   │   │   └── Optgroup.php
            │   │   │   └── VisualSelectOption.php
            │   ├── FormException.php
            │   ├── README.md
            │   ├── Renderer.php
            │   ├── Renderer/
            │   │   ├── CommentGenerator.php
            │   │   ├── CommentGenerator/
            │   │   │   ├── DataType.php
            │   │   │   ├── DataType/
            │   │   │   │   └── Date.php
            │   │   │   │   └── Float.php
            │   │   │   │   └── ISODate.php
            │   │   │   │   └── Integer.php
            │   │   │   │   └── RegexHint.php
            │   │   ├── Element.php
            │   │   ├── ElementCallback.php
            │   │   ├── ElementFilter.php
            │   │   ├── ElementFilter/
            │   │   │   ├── RenderDef.php
            │   │   ├── Registry.php
            │   │   ├── RenderType.php
            │   │   ├── RenderType/
            │   │   │   ├── Button.php
            │   │   │   ├── Default.php
            │   │   │   ├── Group.php
            │   │   │   ├── Header.php
            │   │   │   ├── Hint.php
            │   │   │   ├── Html.php
            │   │   │   ├── LayoutlessGroup.php
            │   │   │   ├── Paragraph.php
            │   │   │   ├── Radio.php
            │   │   │   ├── SelfRenderingGroup.php
            │   │   │   ├── Static.php
            │   │   │   ├── Subheader.php
            │   │   │   ├── Tab.php
            │   │   ├── Sections.php
            │   │   ├── Sections/
            │   │   │   ├── Section.php
            │   │   ├── Tabs.php
            │   │   ├── Tabs/
            │   │   │   └── Tab.php
            │   ├── Rule/
            │   │   ├── Equals.php
            │   ├── Validator.php
            │   ├── Validator/
            │   │   ├── Date.php
            │   │   ├── Float.php
            │   │   ├── ISODate.php
            │   │   ├── Integer.php
            │   │   ├── Percent.php
            │   ├── module-context.yaml
            ├── HTMLElement.php
            ├── Icon.php
            ├── Icons/
            │   ├── IconCollection.php
            │   ├── IconInfo.php
            │   ├── README.md
            ├── Interfaces/
            │   ├── ActivatableInterface.php
            │   ├── Badge.php
            │   ├── Bootstrap.php
            │   ├── Bootstrap/
            │   │   ├── DropdownItem.php
            │   ├── Button.php
            │   ├── ButtonLayoutInterface.php
            │   ├── ButtonSizeInterface.php
            │   ├── CapturableInterface.php
            │   ├── ClientConfirmable.php
            │   ├── Conditional.php
            │   ├── ListBuilderInterface.php
            │   ├── MessageLayoutInterface.php
            │   ├── MessageWrapperInterface.php
            │   ├── NamedItemInterface.php
            │   ├── PageTemplateInterface.php
            │   ├── Renderable.php
            │   ├── StatusElementContainer.php
            │   ├── Statuses/
            │   │   ├── Status.php
            │   ├── TooltipableInterface.php
            ├── ItemsSelector.php
            ├── JSHelper.php
            ├── Label.php
            ├── MarkupEditor.php
            ├── MarkupEditor/
            │   ├── CKEditor.php
            │   ├── README.md
            │   ├── Redactor.php
            │   ├── module-context.yaml
            ├── MarkupEditorInfo.php
            ├── Message.php
            ├── Page.php
            ├── Page/
            │   ├── Breadcrumb.php
            │   ├── Breadcrumb/
            │   │   ├── Item.php
            │   ├── Footer.php
            │   ├── Header.php
            │   ├── Help.php
            │   ├── Help/
            │   │   ├── Item.php
            │   │   ├── Item/
            │   │   │   └── Header.php
            │   │   │   └── Para.php
            │   │   │   └── UnorderedListItem.php
            │   ├── Navigation.php
            │   ├── Navigation/
            │   │   ├── Item.php
            │   │   ├── Item/
            │   │   │   ├── ClickableNavItem.php
            │   │   │   ├── DropdownMenu.php
            │   │   │   ├── ExternalLink.php
            │   │   │   ├── HTML.php
            │   │   │   ├── InternalLink.php
            │   │   │   ├── Search.php
            │   │   ├── LinkItemBase.php
            │   │   ├── MetaNavigation.php
            │   │   ├── MetaNavigation/
            │   │   │   ├── DeveloperMenu.php
            │   │   │   ├── UserMenu.php
            │   │   ├── NavConfigurator.php
            │   │   ├── NavConfigurator/
            │   │   │   ├── MenuConfigurator.php
            │   │   ├── QuickNavigation.php
            │   │   ├── QuickNavigation/
            │   │   │   ├── BaseQuickNavItem.php
            │   │   │   ├── Items/
            │   │   │   │   ├── ScreenNavItem.php
            │   │   │   │   ├── URLNavItem.php
            │   │   │   ├── ScreenItemsContainer.php
            │   │   ├── TextLinksNavigation.php
            │   ├── README.md
            │   ├── RevisionableTitle.php
            │   ├── Section.php
            │   ├── Section/
            │   │   ├── Content.php
            │   │   ├── Content/
            │   │   │   ├── HTML.php
            │   │   │   ├── Heading.php
            │   │   │   ├── Separator.php
            │   │   │   ├── Template.php
            │   │   ├── GroupControls.php
            │   │   ├── SectionsRegistry.php
            │   │   ├── Type/
            │   │   │   └── Default.php
            │   │   │   └── Developer.php
            │   ├── Sidebar.php
            │   ├── Sidebar/
            │   │   ├── Item.php
            │   │   ├── Item/
            │   │   │   ├── Button.php
            │   │   │   ├── Custom.php
            │   │   │   ├── DeveloperPanel.php
            │   │   │   ├── DropdownButton.php
            │   │   │   ├── FormTOC.php
            │   │   │   ├── Message.php
            │   │   │   ├── Separator.php
            │   │   │   ├── Template.php
            │   │   ├── LockableItem.php
            │   ├── StepsNavigator.php
            │   ├── StepsNavigator/
            │   │   ├── Step.php
            │   ├── Subtitle.php
            │   ├── Template.php
            │   ├── Template/
            │   │   ├── Custom.php
            │   ├── Title.php
            │   ├── module-context.yaml
            ├── PaginationRenderer.php
            ├── PrettyBool.php
            ├── PropertiesGrid.php
            ├── PropertiesGrid/
            │   ├── Property.php
            │   ├── Property/
            │   │   ├── Amount.php
            │   │   ├── Boolean.php
            │   │   ├── ByteSize.php
            │   │   ├── DateTime.php
            │   │   ├── Header.php
            │   │   ├── MarkdownGridProperty.php
            │   │   ├── Merged.php
            │   │   ├── Message.php
            │   │   ├── Regular.php
            │   │   ├── TagsGridProperty.php
            │   ├── README.md
            │   ├── module-context.yaml
            ├── QuickSelector.php
            ├── QuickSelector/
            │   ├── Base.php
            │   ├── Container.php
            │   ├── Group.php
            │   ├── Item.php
            ├── README.md
            ├── Renderable.php
            ├── ResourceManager.php
            ├── Statuses.php
            ├── Statuses/
            │   ├── Generic.php
            │   ├── GenericSelectable.php
            │   ├── Selectable.php
            │   ├── Status.php
            ├── StringBuilder.php
            ├── SystemHint.php
            ├── Targets/
            │   ├── BaseTarget.php
            │   ├── ClickTarget.php
            │   ├── URLTarget.php
            ├── Themes.php
            ├── Themes/
            │   ├── BaseTemplates/
            │   │   ├── NavigationTemplate.php
            │   ├── Exception.php
            │   ├── Exception/
            │   │   ├── VariableMissingException.php
            │   ├── README.md
            │   ├── Theme.php
            │   ├── Theme/
            │   │   ├── ContentRenderer.php
            │   ├── module-context.yaml
            ├── TooltipInfo.php
            ├── Traits/
            │   ├── ActivatableTrait.php
            │   ├── ButtonDecoratorInterface.php
            │   ├── ButtonDecoratorTrait.php
            │   ├── ButtonLayoutTrait.php
            │   ├── ButtonSizeTrait.php
            │   ├── CapturableTrait.php
            │   ├── ClientConfirmable.php
            │   ├── Conditional.php
            │   ├── MessageWrapperTrait.php
            │   ├── RenderableGeneric.php
            │   ├── ScriptInjectableInterface.php
            │   ├── ScriptInjectableTrait.php
            │   ├── StatusElementContainer.php
            │   ├── TooltipableTrait.php
            ├── Tree/
            │   ├── README.md
            │   ├── TreeNode.php
            │   ├── TreeRenderer.php
            │   ├── module-context.yaml
            ├── UI.php
            ├── module-context.yaml
        └── Utilities/
            ├── BasicEnum.php
            ├── CallableContainer.php
            ├── JSONUnserializer.php
            ├── JSONUnserializerException.php
            ├── TimeOfDay.php
            ├── UnitsHelper.php
        └── _deprecated/
            └── CollectionCreateScreenInterface.php
            └── CollectionSettingsExtendedInterface.php
            └── DisposableDisposed.php
            └── Exception.php
            └── Screens/
                ├── Action.php
                ├── ActionCollectionCreateScreen.php
                ├── ActionCollectionDeleteScreen.php
                ├── ActionCollectionEditScreen.php
                ├── ActionCollectionListScreen.php
                ├── ActionCollectionRecordScreen.php
                ├── Area.php
                ├── BaseCollectionCreateExtendedScreen.php
                ├── BaseCollectionEditExtendedScreen.php
                ├── Mode.php
                ├── ModeCollectionCreateScreen.php
                ├── ModeCollectionListScreen.php
                ├── ModeCollectionRecordScreen.php
                ├── ScreenInterface.php
                ├── Submode.php
                ├── SubmodeBaseCollectionCreateExtended.php
                ├── SubmodeBaseCollectionEditExtended.php
                ├── SubmodeCollectionCreateScreen.php
                ├── SubmodeCollectionDeleteScreen.php
                ├── SubmodeCollectionEdit.php
                ├── SubmodeCollectionListScreen.php
                ├── SubmodeCollectionRecord.php
                ├── Wizard.php
            └── UnexpectedInstanceType.php

```
---
**File Statistics**
- **Size**: 101.28 KB
- **Lines**: 2064
File: `framework-file-structure.md`
