# Coding Guidelines

## General Conventions

- **Null checks**: prefer `isset($this->property)` over strict `null` comparisons.
- **Exception Workflow**: 
- **Keep It Left**: minimize nesting by returning early from functions.
- **Avoid long functions**: break down complex logic into smaller private methods.
- **Consistent naming**: snake_case for DB fields; camelCase for PHP properties/methods.
- **Short acronyms like "ID"**: Always keep uppercase, e.g., `getID()`,  `getUserID()`.
- **Array Initialization**: Always define arrays with the verbose `array()` syntax.
