# API - File Structure
_SOURCE: API Module File Structure_
# API Module File Structure
###  
```
└── src/
    └── classes/
        └── Application/
            └── API/
                └── APIException.php
                └── APIFoldersManager.php
                └── APIManager.php
                └── APIMethodInterface.php
                └── APIResponseDataException.php
                └── APIUrls.php
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
                └── BaseMethods/
                    ├── BaseAPIMethod.php
                └── Cache/
                    ├── APICacheException.php
                    ├── APICacheManager.php
                    ├── APICacheStrategyInterface.php
                    ├── APIResponseCacheLocation.php
                    ├── CacheableAPIMethodInterface.php
                    ├── CacheableAPIMethodTrait.php
                    ├── Strategies/
                    │   ├── FixedDurationStrategy.php
                    │   ├── ManualOnlyStrategy.php
                    ├── UserScopedCacheInterface.php
                    ├── UserScopedCacheTrait.php
                └── Clients/
                    ├── API/
                    │   ├── APIKeyMethodInterface.php
                    │   ├── APIKeyMethodTrait.php
                    │   ├── Params/
                    │   │   └── APIKeyHandler.php
                    │   │   └── APIKeyParam.php
                    ├── APIClientException.php
                    ├── APIClientFilterCriteria.php
                    ├── APIClientFilterSettings.php
                    ├── APIClientRecord.php
                    ├── APIClientRecordSettings.php
                    ├── APIClientsCollection.php
                    ├── Keys/
                    │   └── APIKeyException.php
                    │   └── APIKeyFilterCriteria.php
                    │   └── APIKeyFilterSettings.php
                    │   └── APIKeyMethods.php
                    │   └── APIKeyRecord.php
                    │   └── APIKeyRecordSettings.php
                    │   └── APIKeysCollection.php
                └── Collection/
                    ├── APICacheLocation.php
                    ├── APIMethodCollection.php
                    ├── APIMethodIndex.php
                └── Connector/
                    ├── AppAPIConnector.php
                    ├── AppAPIMethod.php
                └── Documentation/
                    ├── APIDocumentation.php
                    ├── BaseAPIDocumentation.php
                    ├── Examples/
                    │   ├── JSONMethodExample.php
                    ├── MethodDocumentation.php
                └── ErrorResponse.php
                └── ErrorResponsePayload.php
                └── Events/
                    ├── RegisterAPIIndexCacheListener.php
                    ├── RegisterAPIResponseCacheListener.php
                └── Groups/
                    ├── APIGroupInterface.php
                    ├── FrameworkAPIGroup.php
                    ├── GenericAPIGroup.php
                └── OpenAPI/
                    ├── GetOpenAPISpec.php
                    ├── HtaccessGenerator.php
                    ├── MethodConverter.php
                    ├── OpenAPIGenerator.php
                    ├── OpenAPISchema.php
                    ├── ParameterConverter.php
                    ├── ResponseConverter.php
                    ├── SchemaInferrer.php
                    ├── TypeMapper.php
                └── Parameters/
                    ├── APIParamManager.php
                    ├── APIParameterException.php
                    ├── APIParameterInterface.php
                    ├── BaseAPIParameter.php
                    ├── CommonTypes/
                    │   ├── AliasParameter.php
                    │   ├── AlphabeticalParameter.php
                    │   ├── AlphanumericParameter.php
                    │   ├── DateParameter.php
                    │   ├── EmailParameter.php
                    │   ├── LabelParameter.php
                    │   ├── MD5Parameter.php
                    │   ├── NameOrTitleParameter.php
                    ├── Flavors/
                    │   ├── APIHeaderParameterInterface.php
                    │   ├── APIHeaderParameterTrait.php
                    │   ├── RequiredOnlyParamInterface.php
                    │   ├── RequiredOnlyParamTrait.php
                    ├── Handlers/
                    │   ├── APIHandlerInterface.php
                    │   ├── BaseAPIHandler.php
                    │   ├── BaseParamHandler.php
                    │   ├── BaseParamsHandlerContainer.php
                    │   ├── BaseRuleHandler.php
                    │   ├── ParamHandlerInterface.php
                    │   ├── ParamsHandlerContainerInterface.php
                    │   ├── RuleHandlerInterface.php
                    ├── ParamTypeSelector.php
                    ├── Reserved/
                    │   ├── APIMethodParameter.php
                    │   ├── APIVersionParameter.php
                    ├── ReservedParamInterface.php
                    ├── Rules/
                    │   ├── BaseCustomParamSet.php
                    │   ├── BaseRule.php
                    │   ├── CustomParamSetInterface.php
                    │   ├── ParamSet.php
                    │   ├── ParamSetInterface.php
                    │   ├── RuleInterface.php
                    │   ├── RuleTypeSelector.php
                    │   ├── Type/
                    │   │   └── OrRule.php
                    │   │   └── RequiredIfOtherIsSetRule.php
                    │   │   └── RequiredIfOtherValueEquals.php
                    ├── Type/
                    │   ├── BooleanParameter.php
                    │   ├── IDListParameter.php
                    │   ├── IntegerParameter.php
                    │   ├── JSONParameter.php
                    │   ├── ListParameterTrait.php
                    │   ├── StringListParameter.php
                    │   ├── StringParam/
                    │   │   ├── StringValidations.php
                    │   ├── StringParameter.php
                    ├── Validation/
                    │   ├── BaseParamValidation.php
                    │   ├── ParamValidationInterface.php
                    │   ├── ParamValidationResults.php
                    │   ├── Type/
                    │   │   └── CallbackValidation.php
                    │   │   └── EnumValidation.php
                    │   │   └── RegexValidation.php
                    │   │   └── RequiredValidation.php
                    │   │   └── ValueExistsCallbackValidation.php
                    ├── ValueLookup/
                    │   └── SelectableParamValue.php
                    │   └── SelectableValueParamInterface.php
                    │   └── SelectableValueParamTrait.php
                └── Response/
                    ├── JSONInfoSerializer.php
                    ├── ResponseInterface.php
                └── ResponsePayload.php
                └── Traits/
                    ├── DryRun/
                    │   ├── DryRunAPIParam.php
                    ├── DryRunAPIInterface.php
                    ├── DryRunAPITrait.php
                    ├── JSONRequestInterface.php
                    ├── JSONRequestTrait.php
                    ├── JSONResponseInterface.php
                    ├── JSONResponseTrait.php
                    ├── JSONResponseWithExampleInterface.php
                    ├── JSONResponseWithExampleTrait.php
                    ├── RequestRequestInterface.php
                    ├── RequestRequestTrait.php
                └── User/
                    ├── APIRightsInterface.php
                    ├── APIRightsTrait.php
                └── Utilities/
                    ├── KeyDescription.php
                    ├── KeyPath.php
                    ├── KeyPathInterface.php
                    ├── KeyReplacement.php
                └── Versioning/
                    └── APIVersionInterface.php
                    └── BaseAPIVersion.php
                    └── VersionCollection.php
                    └── VersionedAPIInterface.php
                    └── VersionedAPITrait.php

```
---
**File Statistics**
- **Size**: 10.74 KB
- **Lines**: 218
File: `modules/api/file-structure.md`
