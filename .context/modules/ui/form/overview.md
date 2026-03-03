# UI Form - Overview
_SOURCE: Form README_
# Form README
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── Form/
                └── README.md

```
###  Path: `/src/classes/UI/Form/README.md`

```md
# Form

Handles **form creation, rendering, and server-side validation**. Built on top of HTML_QuickForm2, the form system adds framework-level conventions: element ID prefixing (`f-`), typed validation rules, pluggable renderers, AJAX-aware submit handling, and event hooks such as `FormCreated`.

## Integration

- Entry point: `UI_Form`, typically created via `$ui->createForm('my-form')`.
- Fires `FormCreatedEvent` on the UI event system when a form is instantiated.
- Consumed by: any admin screen that needs user input.

## Folder Overview

| Folder / File | Contents |
|---|---|
| `Element/` | Typed form element classes (text, select, checkbox, date-picker, etc.) |
| `Renderer/` | Pluggable HTML renderers for laying out form elements |
| `Rule/` | Validation rule definitions (required, regex, min/max, …) |
| `Validator/` | Server-side validator orchestration |
| `FormException.php` | Typed exceptions for form errors |
| `Renderer.php` | Base renderer class |
| `Validator.php` | Base validator class |

## Key Concepts

- **Element ID prefix** — all element IDs are automatically prefixed with `f-` to avoid collisions with other page elements.
- **Validation rules** — attached per element; run on submission; errors are reported back to the renderer.
- **Renderers** — swappable; the default renderer outputs a Bootstrap-compatible horizontal layout.
- **AJAX submit** — forms can submit without a full page reload; the framework handles the round-trip transparently.

## Typical Usage

```php
$form = $ui->createForm('edit-product');
$form->addText('name', t('Product name'))->makeRequired();
$form->addSelect('status', t('Status'))->addOptions($statusOptions);

if($form->isSubmitted() && $form->validate()) {
    $values = $form->getValues();
    // ... process $values
}

echo $form->render();
```

> Related: [UI module overview](../README.md)

```
---
**File Statistics**
- **Size**: 2.19 KB
- **Lines**: 68
File: `modules/ui/form/overview.md`
