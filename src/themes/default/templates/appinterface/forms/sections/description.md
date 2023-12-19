Use sections to group form elements together. Unlike traditional
sections, contents do not have to be appended manually. 
Once a section has been added, any elements added to the form are 
automatically added to it. Adding another section will switch to 
that section.

> NOTE: This example uses a generic formable instance, used to
> create forms outside of administration screens. In the application 
> UI, every screen is already a formable. In this case, the form 
> only needs to be initialized with `$this->createFormableForm()`.
