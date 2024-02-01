Marking a tree renderer's items as selectable with the `makeNodeSelectable()`
method adds checkboxes to items, so they can be selected. Nodes can be given
a value with `setValue()`, which is used if the form is submitted.

> If a node is not given any target (url or click statement), its text is 
> automatically used as `<label>` element for the checkbox.

Selected nodes are different from active nodes: A selected node can also be
active, but an active node is not necessarily selected.
