# Array Handling in the Framework

When working with associative arrays in the framework and applications built
using the framework, it is recommended to use the provided utility 
class `ArrayDataCollection`. It offers type-safety and utility methods.

Example:

```php
use AppUtils\ArrayDataCollection;

$data = ArrayDataCollection::create(array(
    'name' => 'John Doe',
    'integer' => 45,
    'booleanString' => 'true', // can also use `yes` and `no`,
    'float' => 3.14,
));

$name = $data->getString('name');
$age = $data->getInt('integer');
$isActive = $data->getBool('booleanString');
$piValue = $data->getFloat('float');
```
