# MarkupEditor

Integrates **WYSIWYG rich-text editors** into forms. The submodule abstracts over two concrete backends so that application code can use a single API regardless of which editor is active.

## Backends

| Class | Editor | Notes |
|---|---|---|
| `UI_MarkupEditor_CKEditor` | **CKEditor 5** | Default; supports a custom JS build, configurable toolbar buttons |
| `UI_MarkupEditor_Redactor` | **Redactor** | Alternative backend |

## Key Classes

| Class | Role |
|---|---|
| `UI_MarkupEditor` | Abstract base — defines the shared API all backends must implement |
| `UI_MarkupEditor_CKEditor` | CKEditor 5 integration; exposes `BUTTON_*` constants for toolbar configuration |
| `UI_MarkupEditor_Redactor` | Redactor integration |
| `UI_MarkupEditorInfo` | Metadata / capability descriptor for a given editor instance |

## CKEditor Toolbar Buttons

`UI_MarkupEditor_CKEditor` exposes `BUTTON_*` string constants for every supported toolbar item (`BUTTON_BOLD`, `BUTTON_LINK`, `BUTTON_BULLETED_LIST`, `BUTTON_ALIGN_*`, etc.), allowing programmatic toolbar composition.

## Template

CKEditor output is driven by the template `ui/markup-editor/ckeditor/command`.

## Typical Usage

```php
// Usually wired through the form element, not instantiated directly
$element = $form->addMarkupEditor('description', t('Description'));
$element->getEditor()->setButtons([
    UI_MarkupEditor_CKEditor::BUTTON_BOLD,
    UI_MarkupEditor_CKEditor::BUTTON_ITALIC,
    UI_MarkupEditor_CKEditor::BUTTON_LINK,
]);
```

> Related: [UI module overview](../README.md) · [Themes and Templates](../Docs/themes-and-templates.md)
