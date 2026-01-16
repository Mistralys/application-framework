# UI Localization

## Best Practices for Localizing User Interface Strings

- **Translation Function**: Wrap user-facing strings with `t()` from the `AppLocalize` package.
- **Placeholder Syntax**: Use placeholders for dynamic content with numbered placeholders, e.g. `%1$s`.
- **Placeholder Behavior**: Placeholders in translation functions work exactly like `sprintf`.
- **Splitting Sentences**: Systematically split sentences into multiple calls to `t()` for maximum reusability of texts.

Example:

```php
function getWelcomeMessage($username) {
    return t('Welcome, %1$s!', $username);
}
```

## Providing Context for Translators

For more complex texts with many placeholders or when the placeholder content is
difficult to infer from the context, context for translators can be added by using 
the `tex()` function. 

Example:

```php
function getOrderSummary($orderNumber, $itemCount, $totalPrice) 
{
    return tex(
        'Your order %1$s contains %2$s items totaling %3$s.',
        'Order summary message with 1) order number, 2) item count, and 3) total price.',
        $orderNumber,
        $itemCount,
        $totalPrice
    );
}
```
