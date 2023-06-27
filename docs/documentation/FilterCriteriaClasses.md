# Filter criteria classes

The filter criteria classes are used to fetch records according to a set
of criteria, like an extended search.

## Flavor: Database

### Adding JOIN statements

#### Adding a mandatory JOIN

```php
class Documentation_FilterCriteria extends Application_FilterCriteria_Database
{
    const JOIN_TARGET = 'join_target';

    protected function _registerJoins() : void
    {
        // If there are no JOIN statements that depend
        // on this one, it can be added directly.
        $this->addJoinStatement(
            "JOIN
                {table_target} AS {target}
            ON
                {target}.{primary_name}={source}.{primary_name}"
        );
        
        // If there are dependent JOINs, it must be
        // registered instead:
        $this->registerJoinStatement(
            self::JOIN_TARGET,
            "JOIN
                {table_target} AS {target}
            ON
                {target}.{primary_name}={source}.{primary_name}"
        );
        
        // To mark it as mandatory, require it by default.
        $this->requireJoin(self::JOIN_TARGET);
    }
}
```
