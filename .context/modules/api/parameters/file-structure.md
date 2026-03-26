# API Parameters - File Structure
_SOURCE: API Parameters File Structure_
# API Parameters File Structure
###  
```
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Parameters/
                    └── APIParamManager.php
                    └── APIParameterException.php
                    └── APIParameterInterface.php
                    └── BaseAPIParameter.php
                    └── CommonTypes/
                        ├── AliasParameter.php
                        ├── AlphabeticalParameter.php
                        ├── AlphanumericParameter.php
                        ├── DateParameter.php
                        ├── EmailParameter.php
                        ├── LabelParameter.php
                        ├── MD5Parameter.php
                        ├── NameOrTitleParameter.php
                    └── Flavors/
                        ├── APIHeaderParameterInterface.php
                        ├── APIHeaderParameterTrait.php
                        ├── RequiredOnlyParamInterface.php
                        ├── RequiredOnlyParamTrait.php
                    └── Handlers/
                        ├── APIHandlerInterface.php
                        ├── BaseAPIHandler.php
                        ├── BaseParamHandler.php
                        ├── BaseParamsHandlerContainer.php
                        ├── BaseRuleHandler.php
                        ├── ParamHandlerInterface.php
                        ├── ParamsHandlerContainerInterface.php
                        ├── RuleHandlerInterface.php
                    └── ParamTypeSelector.php
                    └── Reserved/
                        ├── APIMethodParameter.php
                        ├── APIVersionParameter.php
                    └── ReservedParamInterface.php
                    └── Rules/
                        ├── BaseCustomParamSet.php
                        ├── BaseRule.php
                        ├── CustomParamSetInterface.php
                        ├── ParamSet.php
                        ├── ParamSetInterface.php
                        ├── RuleInterface.php
                        ├── RuleTypeSelector.php
                        ├── Type/
                        │   └── OrRule.php
                        │   └── RequiredIfOtherIsSetRule.php
                        │   └── RequiredIfOtherValueEquals.php
                    └── Type/
                        ├── BooleanParameter.php
                        ├── IDListParameter.php
                        ├── IntegerParameter.php
                        ├── JSONParameter.php
                        ├── StringParam/
                        │   ├── StringValidations.php
                        ├── StringParameter.php
                    └── Validation/
                        ├── BaseParamValidation.php
                        ├── ParamValidationInterface.php
                        ├── ParamValidationResults.php
                        ├── Type/
                        │   └── CallbackValidation.php
                        │   └── EnumValidation.php
                        │   └── RegexValidation.php
                        │   └── RequiredValidation.php
                        │   └── ValueExistsCallbackValidation.php
                    └── ValueLookup/
                        └── SelectableParamValue.php
                        └── SelectableValueParamInterface.php
                        └── SelectableValueParamTrait.php

```
---
**File Statistics**
- **Size**: 3.91 KB
- **Lines**: 84
File: `modules/api/parameters/file-structure.md`
