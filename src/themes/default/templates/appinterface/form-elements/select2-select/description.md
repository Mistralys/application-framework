Attaching the `select2` library to a select element will enable filtering 
without any additional code on a vanilla select element. 

### Serverside

On the server side, this can be enabled on any select element by adding the
`filterable` class to the element (see the `CSSClasses` class for a constant). 
Any select elements with this class will automatically have the `select2` 
library attached to it on page load.

### Clientside

On the client side, this can be enabled on any select element at runtime 
with the FormHelper method:

```javascript
FormHelper.makeSelectFilterable('#elementID');
```
