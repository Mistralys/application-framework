Column controls are a solution to handle complex data grids 
with many columns. It allows selecting how many columns should
be immediately visible, and to navigate left and right through 
the columns.

It also enables other advanced features, such as the full view
which opens a new window with all columns visible.

**Some things you can try:**

- Reduce the number of columns, and navigate through them with 
  the arrow buttons.
- Open the full view to see all columns in a new window.
- Open the grid settings to change column visibility and order.

The "Actions" column, since it is marked as such using 
`roleActions()`, does not scroll. It is always visible and 
cannot be hidden or repositioned by design.

----

> NOTE: Grid settings are user-specific, and they will persist
> in the database. This is tied to the grid name given when
> the object is created, so using the same name will keep the
> same settings.
