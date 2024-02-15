The redirect message helper class intends to simplify the process 
to generate the messages shown after executing an action. 
When processing items, the same basic principle always applies: 
A message is required to inform the user how many items were affected.

Let's say we wish to dry all locally sourced herbs. The helper class 
eliminates the boilerplate code with IF statements required to handle 
the three possible outcomes of the action: 

- No herbs were/could be dried
- A single herb was dried
- Multiple herbs were dried
