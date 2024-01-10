Conditionals are used to define when sidebar elements can be displayed. 
For this, the `requireXXX()` methods are used. If the condition is not
met, the element is automatically hidden.

This makes the sidebar code much more readable and maintainable, as no
`if` statements are required.

In this example, there are two buttons. Both are tied to a custom
boolean application setting, and the buttons are used to toggle the
value.
