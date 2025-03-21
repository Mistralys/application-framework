This shows how to enable the paging controls on a 
data grid with custom entries, which are not loaded from
a database or with the help of a `FilterCriteria` instance.

Instead of letting the database sort the entries, they
are sorted dynamically in PHP by the `DataGrid` instance. 
The drawback being that the full list of entries is required 
to apply the sorting. Once the sorting is done, the entries 
can be sliced to the subset to display in the grid.

> NOTE: Such lists should not be too large, as the full list
> of entries is loaded into memory, even if only 10 records 
> are to be shown. Consider using storage that supports 
> limits for larger lists.

The sorting is done with the `filterAndSortEntries()` method.
