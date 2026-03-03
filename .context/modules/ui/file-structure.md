# UI Module - File Structure
_SOURCE: UI Module File Structure_
# UI Module File Structure
###  
```
└── src/
    └── classes/
        └── UI/
            └── Admin/
                ├── Screens/
                │   └── AppInterfaceDevelMode.php
                │   └── CSSGenDevelMode.php
            └── AdminURLs/
                ├── AdminURL.php
                ├── AdminURLException.php
                ├── AdminURLInterface.php
                ├── AdminURLsInterface.php
                ├── README.md
                ├── module-context.yaml
            └── Badge.php
            └── BaseLockable.php
            └── BaseUIEvent.php
            └── Bootstrap.php
            └── Bootstrap/
                ├── Anchor.php
                ├── BadgeDropdown.php
                ├── BaseDropdown.php
                ├── BigSelection/
                │   ├── BaseItem.php
                │   ├── BigSelectionCSS.php
                │   ├── BigSelectionWidget.php
                │   ├── Item/
                │   │   └── HeaderItem.php
                │   │   └── RegularItem.php
                │   │   └── SeparatorItem.php
                ├── ButtonDropdown.php
                ├── ButtonGroup.php
                ├── ButtonGroup/
                │   ├── ButtonGroupItemInterface.php
                ├── Dropdown/
                │   ├── AJAXLoader.php
                ├── DropdownAnchor.php
                ├── DropdownDivider.php
                ├── DropdownHeader.php
                ├── DropdownMenu.php
                ├── DropdownStatic.php
                ├── DropdownSubmenu.php
                ├── Popover.php
                ├── README.md
                ├── Tab.php
                ├── Tab/
                │   ├── Renderer.php
                │   ├── Renderer/
                │   │   └── Link.php
                │   │   └── Menu.php
                │   │   └── Toggle.php
                ├── Tabs.php
                ├── module-context.yaml
            └── Button.php
            └── CSSClasses.php
            └── CSSGenerator/
                ├── CSSGen.php
                ├── CSSGenException.php
                ├── CSSGenFile.php
                ├── CSSGenLocation.php
            └── ClientConfirmable/
                ├── Message.php
            └── ClientResource.php
            └── ClientResource/
                ├── Javascript.php
                ├── README.md
                ├── Stylesheet.php
                ├── module-context.yaml
            └── ClientResourceCollection.php
            └── CriticalityEnum.php
            └── DataGrid.php
            └── DataGrid/
                ├── Action.php
                ├── Action/
                │   ├── Confirm.php
                │   ├── Default.php
                │   ├── Javascript.php
                │   ├── Separator.php
                ├── BaseListBuilder.php
                ├── Column.php
                ├── Column/
                │   ├── ColumnSettingStorage.php
                │   ├── MultiSelect.php
                ├── Entry.php
                ├── Entry/
                │   ├── Heading.php
                │   ├── Merged.php
                ├── EntryClientCommands.php
                ├── Exception.php
                ├── GridClientCommands.php
                ├── GridConfigurator.php
                ├── ListBuilder/
                │   ├── ListBuilderScreenInterface.php
                │   ├── ListBuilderScreenTrait.php
                ├── README.md
                ├── RedirectMessage.php
                ├── Row.php
                ├── Row/
                │   ├── Sums.php
                │   ├── Sums/
                │   │   └── ColumnDef.php
                ├── module-context.yaml
            └── Docs/
                ├── themes-and-templates.md
                ├── ui-helper-classes.md
            └── Event/
                ├── FormCreatedEvent.php
                ├── PageRendered.php
            └── Exception.php
            └── Form.php
            └── Form/
                ├── Element/
                │   ├── DateTimePicker/
                │   │   ├── BasicTime.php
                │   ├── Datepicker.php
                │   ├── ExpandableSelect.php
                │   ├── HTMLDatePicker.php
                │   ├── HTMLDateTimePicker.php
                │   ├── HTMLTimePicker.php
                │   ├── ImageUploader.php
                │   ├── Multiselect.php
                │   ├── Switch.php
                │   ├── TreeSelect.php
                │   ├── UIButton.php
                │   ├── VisualSelect.php
                │   ├── VisualSelect/
                │   │   └── ImageSet.php
                │   │   └── Optgroup.php
                │   │   └── VisualSelectOption.php
                ├── FormException.php
                ├── README.md
                ├── Renderer.php
                ├── Renderer/
                │   ├── CommentGenerator.php
                │   ├── CommentGenerator/
                │   │   ├── DataType.php
                │   ├── Element.php
                │   ├── ElementCallback.php
                │   ├── ElementFilter.php
                │   ├── ElementFilter/
                │   │   ├── RenderDef.php
                │   ├── Registry.php
                │   ├── RenderType.php
                │   ├── RenderType/
                │   │   ├── Button.php
                │   │   ├── Default.php
                │   │   ├── Group.php
                │   │   ├── Header.php
                │   │   ├── Hint.php
                │   │   ├── Html.php
                │   │   ├── LayoutlessGroup.php
                │   │   ├── Paragraph.php
                │   │   ├── Radio.php
                │   │   ├── SelfRenderingGroup.php
                │   │   ├── Static.php
                │   │   ├── Subheader.php
                │   │   ├── Tab.php
                │   ├── Sections.php
                │   ├── Sections/
                │   │   ├── Section.php
                │   ├── Tabs.php
                │   ├── Tabs/
                │   │   └── Tab.php
                ├── Rule/
                │   ├── Equals.php
                ├── Validator.php
                ├── Validator/
                │   ├── Date.php
                │   ├── Float.php
                │   ├── ISODate.php
                │   ├── Integer.php
                │   ├── Percent.php
                ├── module-context.yaml
            └── HTMLElement.php
            └── Icon.php
            └── Interfaces/
                ├── ActivatableInterface.php
                ├── Badge.php
                ├── Bootstrap.php
                ├── Bootstrap/
                │   ├── DropdownItem.php
                ├── Button.php
                ├── ButtonLayoutInterface.php
                ├── ButtonSizeInterface.php
                ├── CapturableInterface.php
                ├── ClientConfirmable.php
                ├── Conditional.php
                ├── ListBuilderInterface.php
                ├── MessageLayoutInterface.php
                ├── MessageWrapperInterface.php
                ├── NamedItemInterface.php
                ├── PageTemplateInterface.php
                ├── Renderable.php
                ├── StatusElementContainer.php
                ├── Statuses/
                │   ├── Status.php
                ├── TooltipableInterface.php
            └── ItemsSelector.php
            └── JSHelper.php
            └── Label.php
            └── MarkupEditor.php
            └── MarkupEditor/
                ├── CKEditor.php
                ├── README.md
                ├── Redactor.php
                ├── module-context.yaml
            └── MarkupEditorInfo.php
            └── Message.php
            └── Page.php
            └── Page/
                ├── Breadcrumb.php
                ├── Breadcrumb/
                │   ├── Item.php
                ├── Footer.php
                ├── Header.php
                ├── Help.php
                ├── Help/
                │   ├── Item.php
                │   ├── Item/
                │   │   └── Header.php
                │   │   └── Para.php
                │   │   └── UnorderedListItem.php
                ├── Navigation.php
                ├── Navigation/
                │   ├── Item.php
                │   ├── Item/
                │   │   ├── ClickableNavItem.php
                │   │   ├── DropdownMenu.php
                │   │   ├── ExternalLink.php
                │   │   ├── HTML.php
                │   │   ├── InternalLink.php
                │   │   ├── Search.php
                │   ├── LinkItemBase.php
                │   ├── MetaNavigation.php
                │   ├── MetaNavigation/
                │   │   ├── DeveloperMenu.php
                │   │   ├── UserMenu.php
                │   ├── NavConfigurator.php
                │   ├── NavConfigurator/
                │   │   ├── MenuConfigurator.php
                │   ├── QuickNavigation.php
                │   ├── QuickNavigation/
                │   │   ├── BaseQuickNavItem.php
                │   │   ├── ScreenItemsContainer.php
                │   ├── TextLinksNavigation.php
                ├── README.md
                ├── RevisionableTitle.php
                ├── Section.php
                ├── Section/
                │   ├── Content.php
                │   ├── Content/
                │   │   ├── HTML.php
                │   │   ├── Heading.php
                │   │   ├── Separator.php
                │   │   ├── Template.php
                │   ├── GroupControls.php
                │   ├── SectionsRegistry.php
                │   ├── Type/
                │   │   └── Default.php
                │   │   └── Developer.php
                ├── Sidebar.php
                ├── Sidebar/
                │   ├── Item.php
                │   ├── Item/
                │   │   ├── Button.php
                │   │   ├── Custom.php
                │   │   ├── DeveloperPanel.php
                │   │   ├── DropdownButton.php
                │   │   ├── FormTOC.php
                │   │   ├── Message.php
                │   │   ├── Separator.php
                │   │   ├── Template.php
                │   ├── LockableItem.php
                ├── StepsNavigator.php
                ├── StepsNavigator/
                │   ├── Step.php
                ├── Subtitle.php
                ├── Template.php
                ├── Template/
                │   ├── Custom.php
                ├── Title.php
                ├── module-context.yaml
            └── PaginationRenderer.php
            └── PrettyBool.php
            └── PropertiesGrid.php
            └── PropertiesGrid/
                ├── Property.php
                ├── Property/
                │   ├── Amount.php
                │   ├── Boolean.php
                │   ├── ByteSize.php
                │   ├── DateTime.php
                │   ├── Header.php
                │   ├── MarkdownGridProperty.php
                │   ├── Merged.php
                │   ├── Message.php
                │   ├── Regular.php
                │   ├── TagsGridProperty.php
                ├── README.md
                ├── module-context.yaml
            └── QuickSelector.php
            └── QuickSelector/
                ├── Base.php
                ├── Container.php
                ├── Group.php
                ├── Item.php
            └── README.md
            └── Renderable.php
            └── ResourceManager.php
            └── Statuses.php
            └── Statuses/
                ├── Generic.php
                ├── GenericSelectable.php
                ├── Selectable.php
                ├── Status.php
            └── StringBuilder.php
            └── SystemHint.php
            └── Targets/
                ├── BaseTarget.php
                ├── ClickTarget.php
                ├── URLTarget.php
            └── Themes.php
            └── Themes/
                ├── BaseTemplates/
                │   ├── NavigationTemplate.php
                ├── Exception.php
                ├── Exception/
                │   ├── VariableMissingException.php
                ├── README.md
                ├── Theme.php
                ├── Theme/
                │   ├── ContentRenderer.php
                ├── module-context.yaml
            └── TooltipInfo.php
            └── Traits/
                ├── ActivatableTrait.php
                ├── ButtonDecoratorInterface.php
                ├── ButtonDecoratorTrait.php
                ├── ButtonLayoutTrait.php
                ├── ButtonSizeTrait.php
                ├── CapturableTrait.php
                ├── ClientConfirmable.php
                ├── Conditional.php
                ├── MessageWrapperTrait.php
                ├── RenderableGeneric.php
                ├── ScriptInjectableInterface.php
                ├── ScriptInjectableTrait.php
                ├── StatusElementContainer.php
                ├── TooltipableTrait.php
            └── Tree/
                ├── README.md
                ├── TreeNode.php
                ├── TreeRenderer.php
                ├── module-context.yaml
            └── UI.php
            └── module-context.yaml

```
---
**File Statistics**
- **Size**: 15.34 KB
- **Lines**: 369
File: `modules/ui/file-structure.md`
