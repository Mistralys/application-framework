# BigSelection — Checkable items

Demonstrates a `BigSelectionWidget` with checkable items wrapped in a `<form>`.

Each item is rendered as a toggle: clicking an item checks or unchecks it.
Checked items submit their value as part of the surrounding form, via
a hidden `name[]` input that has its `disabled` attribute removed on check.

The visual checkbox indicator uses two server-side-rendered FontAwesome circle
icons: `itemInactive` (outline circle, `far fa-circle`) for the unchecked state
and `itemActive` (solid circle, `fa fa-circle`) for the checked state. CSS
toggles which icon is visible based on the `active` class on the parent `<li>`;
JavaScript only manages that state class.

The widget requires a form name (`setFormName()` / `createBigSelection($name)`) to
be set before rendering. On the server side, call `getSubmittedValues()` to
retrieve and validate only the values that were registered as checkable items,
filtering out any values injected by the client.
