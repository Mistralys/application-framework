# Tree

Renders a **hierarchical tree widget** composed of nestable nodes. Nodes support icons, clickable targets (URL or JavaScript), active/selected states, and per-node action buttons.

## Integration

- Entry point: `UI\Tree\TreeNode` — build the root node and attach children.
- A `TreeRenderer` translates the node graph into HTML.
- Nodes implement `Application_Interfaces_Iconizable`, so any framework icon can be assigned to a node.
- Link targets use the `UI\Targets` abstraction (`URLTarget`, `ClickTarget`), keeping URL and JS interactions consistent with the rest of the UI layer.

## Key Classes

| Class | Role |
|---|---|
| `TreeNode` | A single node: label, optional link target, child nodes, action buttons |
| `TreeRenderer` | Walks the node graph and emits the HTML tree |

## `TreeNode` Key API

| Method | Purpose |
|---|---|
| `addChild(string $label)` | Creates and returns a child `TreeNode` |
| `setURLTarget(AdminURLInterface)` | Makes the node a clickable link |
| `setClickTarget(string $js)` | Makes the node trigger a JS expression |
| `setActive(bool)` | Marks the node as the currently active branch |
| `setSelected(bool)` | Marks the node as the selected item |
| `addAction(UI_Button)` | Attaches an action button rendered beside the label |
| `setIcon(…)` | Assigns a FontAwesome icon via the Iconizable trait |

## Typical Usage

```php
$root = new TreeNode($ui, t('Categories'));

$node = $root->addChild(t('Electronics'));
$node->setURLTarget(AdminURL::create()->area('Products')->param('cat', 'electronics'));
$node->setActive(true);

$node->addChild(t('Phones'))->setSelected(true);
$node->addChild(t('Laptops'));

echo (new TreeRenderer($root))->render();
```

> Related: [UI module overview](../README.md) · [AdminURLs](../AdminURLs/README.md)
