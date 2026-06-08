# Admin - File Structure
<INSTRUCTION>
# Admin Module — File Structure

</INSTRUCTION>
------------------------------------------------------------
_SOURCE: Admin module file tree_
# Admin module file tree
###  
```
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── AdminException.php
                └── AdminScreenStubInterface.php
                └── Area/
                    ├── BaseMode.php
                    ├── Events/
                    │   ├── UIHandlingCompleteEvent.php
                    ├── Mode/
                    │   └── BaseSubmode.php
                    │   └── Submode/
                    │       └── BaseAction.php
                └── BaseArea.php
                └── BaseScreenRights.php
                └── ClassLoaderScreenInterface.php
                └── ClassLoaderScreenTrait.php
                └── Index/
                    ├── API/
                    │   ├── DescribeAdminAreasAPIInterface.php
                    │   ├── Methods/
                    │   │   └── DescribeAdminAreasAPI.php
                    ├── AdminScreenIndex.php
                    ├── AdminScreenIndexer.php
                    ├── AdminScreenInfoCollector.php
                    ├── ScreenDataInterface.php
                    ├── Screens/
                    │   ├── SitemapMode.php
                    ├── StubArea.php
                    ├── StubMode.php
                    ├── StubSubmode.php
                └── RequestTypes/
                    ├── BaseRequestType.php
                    ├── RequestTypeInterface.php
                └── ScreenException.php
                └── ScreenRightsContainerInterface.php
                └── ScreenRightsContainerTrait.php
                └── ScreenRightsInterface.php
                └── Screens/
                    ├── Events/
                    │   └── ActionsHandledEvent.php
                    │   └── BaseScreenEvent.php
                    │   └── BeforeActionsHandledEvent.php
                    │   └── BeforeBreadcrumbHandledEvent.php
                    │   └── BeforeContentRenderedEvent.php
                    │   └── BeforeSidebarHandledEvent.php
                    │   └── BreadcrumbHandledEvent.php
                    │   └── ContentRenderedEvent.php
                    │   └── SidebarHandledEvent.php
                └── Skeleton.php
                └── Traits/
                    ├── DevelModeInterface.php
                    ├── DevelModeTrait.php
                └── URL.php
                └── Welcome/
                    ├── Events/
                    │   ├── BaseWelcomeQuickNavListener.php
                    │   ├── WelcomeQuickNavEvent.php
                    ├── Screens/
                    │   ├── OverviewMode.php
                    │   ├── SettingsMode.php
                    │   ├── WelcomeArea.php
                    ├── WelcomeManager.php
                └── Wizard/
                    ├── BaseWizardMode.php
                    ├── InvalidationHandler.php
                    ├── Step.php
                    ├── WizardConfigurator.php
                    ├── WizardPreselection.php
                └── WizardException.php

```