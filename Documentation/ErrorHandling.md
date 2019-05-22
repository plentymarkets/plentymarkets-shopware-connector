# Error Handling
This Document should give a brief overview how and where the connector handles errors

## Reading Phase
During the reading phase of the connector, errors with different levels could occur. 

### Exceptions / Warnings
The corresponding object can not be processed.

### Notice
The corresponding object will be processed, but the data in question will be skipped.

### No Message
Some Settings could lead to an exclusion of an object. These conditions will not be logged as they are part of
the adapter domain.

## Persisting Phase
Errors can also occur during the write phase. Throwing exceptions will be avoided at all cost.

### Exceptions / Warnings
The corresponding object can not be processed.
