# Error Handling
This Document should give a brief overview how and where the connector handels errors


## Reading Phase
During the reading phase of the connector, errors with different levels could potentially occour. 

### Exceptions / Warnings
The corresponding object can not be processed.

### Notice
The corresponding object will be processed, but the data in question will be skipped.

### No Message
Some Settings could lead to an exclusion of an object. These conditions will not be logged as they are part of
the adapter domain


## Persiting Phase
During the write phase errors can also occour, but will be kept at a bare minimum.

### Exceptions / Warnings
The corresponding object can not be processed.
