When paging is enabled in the data grid, the filter criteria instance
(which is used to fetch records from the DB) can be configured automatically 
from the grid instance. The filter's `configure()` method handles the offset,
limit and sorting according to the current position in the list.

> NOTE: Choosing sort column and direction does not work in the example
> because the database transaction (see Data Grid setup) is rolled back.
