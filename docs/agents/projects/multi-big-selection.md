# Project: Multi-Selection Big Selection

## Description

In the framework, I would like to add a multi-select mode to the BigSelection UI element to allow selecting multiple values, akin to options in a select element. 

The entries will get checkboxes before their labels to signify that they can be checked.

## Setup

Item types should be freely mixable. Example scenario with the following items:

1. A regular clickable item as default entry
2. A separator
3. Checkable item A
4. Checkable item B

[Submit button]

> This then lets the user click the first item if they just want to use the defaults, or take their pick from the checkable items.

## Value Handling

A form tag is implied to exist around the big selection to capture its values. The resulting form variable should be an indexed array containing the checked values, or an empty array if none were selected.

Mandatory is setting a name for the form element: A method like `setFormName()` can be used for this, and if the BigSelection contains checkable items and no name has been set, an exception is thrown. Done this way to avoid adding the form element name to every call used to add a checkable element.

A getter method enables accessing the value from the request if available, validated against the possible values - must be called after initialization of the elements and after calling `setFormName()`. 

In addition to `setFormName()`, a form name should be optional when creating a new BigSelection instance.

## Big Selection Examples

`application-framework/src/themes/default/templates/appinterface/selection-lists`

The following example subdirectories were added as part of this feature:

| Slug | Directory | Description |
|---|---|---|
| `selection-lists.checkable` | `checkable/` | Basic checkable items with no pre-selection. |
| `selection-lists.checkable-preselected` | `checkable-preselected/` | Checkable items with `makeSelected()` applied to pre-select some items on page load. |
| `selection-lists.checkable-mixed` | `checkable-mixed/` | Mixed widget combining header, regular linked items, separator, and checkable items. |