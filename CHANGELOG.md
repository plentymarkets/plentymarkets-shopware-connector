# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [## [unreleased]]
### Added
- curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4) @florianliebig

## [## [5.3.3]]
### Added
- additional manufacturer information as attributes (@Pfabeck & @ugurkankya)
- set plentymarkets system currency for payments @Pfabeck

## [## [5.3.2]]
### Fixed
- set iterator limit back to 50 (@Pfabeck)

## [## [5.3.1]]
### Fixed
- fix cascade persist error on status update (@Pfabeck)

### Changed
- set iterator limit to 100 (@Pfabeck)

## [## [5.3.0]]
### Fixed
- fix handling of important data on item sync (@lacodimizer)
- fix tax handling on order export (@lacodimizer)
- fixed wrong float cast (@ArvatisJohannes)
- SW 5.6 required
- PHP 7.2 required

## [## [5.2.1]]
### Fixed
- fix sorting of product images (@lacodimizer)

### Changed
- tax handling refactored (@Pfabeck)
- remove item variation id at vouchers and discounts @jmanz

## [## [5.2.0]]
### Fixed
- fix category sync if more then one shop has the same root category (@lacodimizer)
- fix getNumber() on boolean bug (@Pfabeck)
- fix error after changed mainvariationnumber (@Pfabeck)
- fix multipack articles (@Pfabeck)
- fix price referrer (@Pfabeck)

### Added
- article image translation (@Pfabeck)
- amazonPay by BestIT integration (@Pfabeck)
- export custom products options as order items (@Pfabeck)
- transmit surcharge as item option (@Pfabeck)
- separate price import logic (@Pfabeck)
- bundle variation limit (@Pfabeck)
- variant properties are now transferred as attribute (beta feature) (@Pfabeck)
- 3rd gender support (@Pfabeck)

### Changed
- change media command handler to update media if exists (@lacodimizer)
- optimize performance of item sync (@lacodimizer)
- PHP 7.1 required

## [## [5.1.0]]
### Fixed
- transfer payment information without a real transactionid if needed
- fix last stock for variations (sw >= 5.4.x)
- fix product category relations, if the shop category is not directly behind the shopware root category (@lacodimizer)
- fix config for checking inactive main variation (note: if the config was set and the main variation is inactive, the product will be set inactive. if the the config is not active, the product will be active, if >= one variation is active) (@lacodimizer)
- fix product main variation relation ship (@lacodimizer)
- fix product active state on variation sync(@lacodimizer)
- fix variation sync with wrong product association like images (@lacodimizer)
- retrieve variation properties
- order validation warning 
- duplicated config entries 

### Changed
- sepa payment informations are now transfered even without a account holder (@jkrzefski)
- raised the minimal shopware version to 5.5 (@jochenmanz)
- extracted all database operations in the IdentityService into a own storage class (@jochenmanz)
- extracted all database operations in the ConfigService into a own storage class (@jochenmanz)
- extracted all database operations in the BacklogService into a own storage class (@jochenmanz)
- change cronjob scheduler to run not parallel cronjobs to avoid errors (@lacodimizer)
- change product edit date time if the variation sync updates variations of an product (@lacodimizer)

### Added
- paypal unified plugin integration
- added a dump command for debugging purpose

## [## [5.0.0]]
### Fixed
- fixed technicalDescription translations (@lacodimizer)
- fixed mandant shop product activation (@lacodimizer)
- fixed duplicate product seo categories (@lacodimizer)
- the media category sync was called multiple times instead of only one time (@jochenmanz)
- skip order without customer
- set correct bundle position
- fix product meta title and meta description translation
- fix product duplicates
- fix services.xml for shopware 5.5.x
- fix don't write already transfered payments and orders into backlog

### Changed
- translated variation configurator 
- reference amount will not be scaled down
- translated short and technical description 
- removed comment and address rest call
- optimized the stock handling performance
- restructured the services.xml files
- changed the core connector namespace to SystemConnector (@jochenmanz)

### Added
- transfer age restriction as attribute to shopware
- transfer top seller badge to shopware
- add product configuration positions of the groups and their values (@lacodimizer)
- add item sync into the backend gui (@lacodimizer)
- handle commands are now prioritised according to their definitions priority
- added a new services.xml for the connector core
- introduced a DefinitionProvider to store and handle definitions
- added multiple product properties (@lacodimizer)

## [4.6.0]
### Fixed
- paypal installment validation error
- item notification is set correctly
- order status is set correctly

### Changed
- removed shipping profiles rest call in item read api
- removed properties rest call in productresponseparser

## [4.5.1]
### Fixed
- paymentstatus is set correctly
- fixed category image import
- fixed price origin configuration 

## [4.5.0]
## warning ##
- complete product import necessary after update, otherwise images will be removed by cleanup cron!

### Fixed
- fixed cdn problem (images have disappeared)
- fixed search for an existing

### Changed

## [4.4.0]
### Added
- transfer category description 2 as attribute to shopware
- optional check if mainvariation is active

### Fixed
- fix salutation in order and customer
- paypal invoice and paypal installment payment data was discarded, the data is now transfered correctly
- automatically set changed date time field for products
- import images without md5Checksum and use filename for adapterIdentifier hash generation
- optional origin-check for price-import (@jppeter)

### Changed
- corrected the name of the isMappedIdentity function of the IdentityService
- corrected the order export with bundles
- get only visible variations of mapped plentymarkets clients

## [4.3.0]
### Added
- fallback for the import of the weight (@jppeter)

### Fixed
- fix tax calculation issue for third country zero tax orders
- prepareOrderItems validation fix 
- only transfer variations with valid clientid
- import alternate media text as name if is set
- import correct main translation
- use correct item tax when transferring order to plenty
- prevent the import of pseudoprices that are equal to the usual price (@jppeter)
- fix of orderstatus and paymentstatus mapping (@smxvh)
- fix bundle import (swagbundle 5.X.X required)

### Changed
- changed the sequence in which the definitions are processed, orders and payments are now fetched first.
- reset unused Shopware attributes for shipping profiles
- handle Shopware's DateTime attributes correctly
- sw 5.3.x required

## [4.2.1]
### Fixed
- don't transfer payment to plenty when payment with same transaction id exists

### Changed
- Category content will not be overwritten by default plenty ID

## [4.2.0]
### Fixed
- error handling when parsing order addresses
- Bundle stock fix (@marcmanusch)
- use correct tax when transferring order to plenty
- error by mediaCategory create
- valid variation weight

### Changed
- set the product inactive if the main variation is inactive, too

## [4.1.0]
### Added
- added a reference amount calculator to calculate the right base content
- transfer item short description as attribute to shopware
- convert gram to kg (item weight)
- get correct category translation

### Fixed
- use correct shop id when parsing shopware orders

### Changed
- optimized the item query performance
- better error handling for commands and cronjobs
- renamed the PlentymarketsAdapter services from plentmarkets_adapter to plentymarkets_adapter

## [4.0.8]
### Fixed
- added a missing use statement in plentys media response parser

## [4.0.7]
### Fixed
- added a missing use statement in CategoryResponseParser

### Changed
- optimized the hash generation for media files
- order item types for discounts and surcharges changed from product to discount and surcharge

## [4.0.6]
### Fixed
- wrong customer langauge was used when parsing orders
- processing a single payment via commandline was not possible

## [4.0.5]
### Fixed
- force category position to integer

## [4.0.4]
### Added
- prepayment is now mapped correctly

### Fixed
- settings could not be saved in some shopware versions
- warehouse selection for item stock calculation was using the wrong identifier

### Changed
- client will retry on 500 errors, too
- backlog service transaction optimization

## [4.0.3]
### Fixed
- processing of sepa payments was not possible

## [4.0.2]
### Fixed
- missing use statement when handling order updates
- fix plenty order status collection

## [4.0.1]
### Fixed
- a wrong assert class was used when creating the transfer object command

## [4.0.0]
### Added
- configurable item configurator set type
- configurable variation number field
- configurable order origin (referrer)
- configurable item warehouse for item stock calculation
- configurable item notification settings
- separate stock import logic
- added a progressbar for all commandline operations
- commands will be added to a new backlog for later processing
- added a backlog processing command and cronjob

### Fixed
- optimized the update path for versions before 2.0
- fix for products not able to persist due too detached entities
- the field order origin (Auftragsherkunft) was ignored and the default was used
- the orer shipping profile was ignored and a default was used

### Changed
- use the whole media data for the hash creation, this enforces changes also when media text fields change
- restructure the backend snippets
- separated the variations from products
- separated stock informations from variations
- only product properties which have the flag "searchable" set to true are now imported
- set order item type to TYPE_UNASSIGEND_VARIATION for plentymarkets if no variation could be found
- better error handling for all query handlers
- added a new progressbar to every query 
- removed a undefinied notice when parsing prices
- only warehouses of type sales will be used for stock calculation
- optimize plentymarkets api client error handling
- Removed all separate commands and queries in favour of a generalized command and query

### Removed
- removed the obsolete process order cronjob

## [3.0.0]
### Fixed
- price configurations with multiple selected customer groups
- use correct shop id for orders

### Changed
- remove media on update to prevent thumbnail corruption
- only update media files if file hash changes
- use array syntax for order querys
- use iterator to fetch mapping informations from plentymarkets
- require interfaces instead of concrete classes in commands
- default to product if order line has a unsupported type

## [2.2.0]
### Added
- translation for backend modul
- extended cache reload on activate
- translation of properties and property values for shopware

### Changed
- optimized the cleanup service to be more error resistent
- baseprice referenceAmount defaults to 1.0 instead of the item
- use correct category position and image for translations
- use all translations if no direct translation is found for categories
- ignore case of object types when searching definitions
- use voucher number as order item name

### Fixed
- fatal error when variation unit is not mapped

## [2.1.0]
### Added
- composer installer support
- introduced a new variation request generator

### Changed
- order reimport performance optimization
- enforce a manufacturer when importing products to shopware
- remove old plentyconnector tables on installation, too
- check for order existance by shop to avoid duplications
- product image handling performance improved
- enhance the backend rest api settings description
- export order discounts and payment surcharges as position
- check for payment existence before exporting payments
- added a account holder field to payments
- always export shipping costs to prevent plenty from using the default settings
- properties are not filteres by default to enable configuration in shopware
- ignore pseudo prices if equal or lower then the normal price

### Fixed
- shipping times without a value will now be casted to 0
- use correct get parameter for changed orders
- only the category translations with a matching shop are now pulled
- sepa payment data was ignored
- use correct property field for payments

## [2.0.3]
### Added
- remove old tables on update from 1.9.4

### Fixed
- product image alternate name was ignored

## [2.0.2]
### Changed
- bump version number for shopware store release

## [2.0.1]
### Changed
- overhauled the price parsing
- refaktored the plenty order handler

## [2.0.0]
### Added
- Order status and shipping number import functionality
- display mapping console command
- category metaRobots are synced to a shopware attribute
- shipping costs vatRate is calculated now
- added reference unit synchronization
- sync product packaging units and content
- added a separate tag for definitions related to cleanup

### Changed
- 1:N mapping of entities instead of 1:1
- categories support multiple shop identifiers instead of a single shop identitifer
- the category field shortDescription will now be synced with the field cmsTitle. 
- the category field longDescription will now be synced with the field cmsText. 
- the category fields meta* are now transfered
- product images can now limited to a shop
- reduced amount of calls during the product sync by 2 per item
- removed the product image shop setting workaround
- optimized the item cross selling retrieving
- categories with no active shop assignment will be deactivated
- order item vatRate is now synced correctly
- added a isMapppedIdentity function to the identityService
- implemented the isMapppedIdentity in all parsers to reduce the amount of objects transferred
- sync model field to shopware's supplierNumber

### Fixed
- product translations

### Removed
- variation packaging unit

## [2.0.0-rc1]
### Added
- complete rewrite using the plenty rest api
=======
# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased]
### Fixed
- fixed technicalDescription translations (@lacodimizer)
- the media category sync was called multiple times instead of only one time

### Changed
- translated variation configurator 
- reference amount will not be scaled down
- translated short and technical description 
- removed comment and address rest call
- add position of variation configurations

### Added
- transfer age restriction as attribute to shopware
- add item properties with type "empty"

## [4.6.0]
### Fixed
- paypal installment validation error
- item notification is set correctly
- order status is set correctly

### Changed
- removed shipping profiles rest call in item read api
- removed properties rest call in productresponseparser

## [4.5.1]
### Fixed
- paymentstatus is set correctly
- fixed category image import
- fixed price origin configuration 

## [4.5.0]
## warning ##
- complete product import necessary after update, otherwise images will be removed by cleanup cron!

### Fixed
- fixed cdn problem (images have disappeared)
- fixed search for an existing

### Changed

## [4.4.0]
### Added
- transfer category description 2 as attribute to shopware
- optional check if mainvariation is active

### Fixed
- fix salutation in order and customer
- paypal invoice and paypal installment payment data was discarded, the data is now transfered correctly
- automatically set changed date time field for products
- import images without md5Checksum and use filename for adapterIdentifier hash generation
- optional origin-check for price-import (@jppeter)

### Changed
- corrected the name of the isMappedIdentity function of the IdentityService
- corrected the order export with bundles
- get only visible variations of mapped plentymarkets clients

## [4.3.0]
### Added
- fallback for the import of the weight (@jppeter)

### Fixed
- fix tax calculation issue for third country zero tax orders
- prepareOrderItems validation fix 
- only transfer variations with valid clientid
- import alternate media text as name if is set
- import correct main translation
- use correct item tax when transferring order to plenty
- prevent the import of pseudoprices that are equal to the usual price (@jppeter)
- fix of orderstatus and paymentstatus mapping (@smxvh)
- fix bundle import (swagbundle 5.X.X required)

### Changed
- changed the sequence in which the definitions are processed, orders and payments are now fetched first.
- reset unused Shopware attributes for shipping profiles
- handle Shopware's DateTime attributes correctly
- sw 5.3.x required

## [4.2.1]
### Fixed
- don't transfer payment to plenty when payment with same transaction id exists

### Changed
- Category content will not be overwritten by default plenty ID

## [4.2.0]
### Fixed
- error handling when parsing order addresses
- Bundle stock fix (@marcmanusch)
- use correct tax when transferring order to plenty
- error by mediaCategory create
- valid variation weight

### Changed
- set the product inactive if the main variation is inactive, too

## [4.1.0]
### Added
- added a reference amount calculator to calculate the right base content
- transfer item short description as attribute to shopware
- convert gram to kg (item weight)
- get correct category translation

### Fixed
- use correct shop id when parsing shopware orders

### Changed
- optimized the item query performance
- better error handling for commands and cronjobs
- renamed the PlentymarketsAdapter services from plentmarkets_adapter to plentymarkets_adapter

## [4.0.8]
### Fixed
- added a missing use statement in plentys media response parser

## [4.0.7]
### Fixed
- added a missing use statement in CategoryResponseParser

### Changed
- optimized the hash generation for media files
- order item types for discounts and surcharges changed from product to discount and surcharge

## [4.0.6]
### Fixed
- wrong customer langauge was used when parsing orders
- processing a single payment via commandline was not possible

## [4.0.5]
### Fixed
- force category position to integer

## [4.0.4]
### Added
- prepayment is now mapped correctly

### Fixed
- settings could not be saved in some shopware versions
- warehouse selection for item stock calculation was using the wrong identifier

### Changed
- client will retry on 500 errors, too
- backlog service transaction optimization

## [4.0.3]
### Fixed
- processing of sepa payments was not possible

## [4.0.2]
### Fixed
- missing use statement when handling order updates
- fix plenty order status collection

## [4.0.1]
### Fixed
- a wrong assert class was used when creating the transfer object command

## [4.0.0]
### Added
- configurable item configurator set type
- configurable variation number field
- configurable order origin (referrer)
- configurable item warehouse for item stock calculation
- configurable item notification settings
- separate stock import logic
- added a progressbar for all commandline operations
- commands will be added to a new backlog for later processing
- added a backlog processing command and cronjob

### Fixed
- optimized the update path for versions before 2.0
- fix for products not able to persist due too detached entities
- the field order origin (Auftragsherkunft) was ignored and the default was used
- the orer shipping profile was ignored and a default was used

### Changed
- use the whole media data for the hash creation, this enforces changes also when media text fields change
- restructure the backend snippets
- separated the variations from products
- separated stock informations from variations
- only product properties which have the flag "searchable" set to true are now imported
- set order item type to TYPE_UNASSIGEND_VARIATION for plentymarkets if no variation could be found
- better error handling for all query handlers
- added a new progressbar to every query 
- removed a undefinied notice when parsing prices
- only warehouses of type sales will be used for stock calculation
- optimize plentymarkets api client error handling
- Removed all separate commands and queries in favour of a generalized command and query

### Removed
- removed the obsolete process order cronjob

## [3.0.0]
### Fixed
- price configurations with multiple selected customer groups
- use correct shop id for orders

### Changed
- remove media on update to prevent thumbnail corruption
- only update media files if file hash changes
- use array syntax for order querys
- use iterator to fetch mapping informations from plentymarkets
- require interfaces instead of concrete classes in commands
- default to product if order line has a unsupported type

## [2.2.0]
### Added
- translation for backend modul
- extended cache reload on activate
- translation of properties and property values for shopware

### Changed
- optimized the cleanup service to be more error resistent
- baseprice referenceAmount defaults to 1.0 instead of the item
- use correct category position and image for translations
- use all translations if no direct translation is found for categories
- ignore case of object types when searching definitions
- use voucher number as order item name

### Fixed
- fatal error when variation unit is not mapped

## [2.1.0]
### Added
- composer installer support
- introduced a new variation request generator

### Changed
- order reimport performance optimization
- enforce a manufacturer when importing products to shopware
- remove old plentyconnector tables on installation, too
- check for order existance by shop to avoid duplications
- product image handling performance improved
- enhance the backend rest api settings description
- export order discounts and payment surcharges as position
- check for payment existence before exporting payments
- added a account holder field to payments
- always export shipping costs to prevent plenty from using the default settings
- properties are not filteres by default to enable configuration in shopware
- ignore pseudo prices if equal or lower then the normal price

### Fixed
- shipping times without a value will now be casted to 0
- use correct get parameter for changed orders
- only the category translations with a matching shop are now pulled
- sepa payment data was ignored
- use correct property field for payments

## [2.0.3]
### Added
- remove old tables on update from 1.9.4

### Fixed
- product image alternate name was ignored

## [2.0.2]
### Changed
- bump version number for shopware store release

## [2.0.1]
### Changed
- overhauled the price parsing
- refaktored the plenty order handler

## [2.0.0]
### Added
- Order status and shipping number import functionality
- display mapping console command
- category metaRobots are synced to a shopware attribute
- shipping costs vatRate is calculated now
- added reference unit synchronization
- sync product packaging units and content
- added a separate tag for definitions related to cleanup

### Changed
- 1:N mapping of entities instead of 1:1
- categories support multiple shop identifiers instead of a single shop identitifer
- the category field shortDescription will now be synced with the field cmsTitle. 
- the category field longDescription will now be synced with the field cmsText. 
- the category fields meta* are now transfered
- product images can now limited to a shop
- reduced amount of calls during the product sync by 2 per item
- removed the product image shop setting workaround
- optimized the item cross selling retrieving
- categories with no active shop assignment will be deactivated
- order item vatRate is now synced correctly
- added a isMapppedIdentity function to the identityService
- implemented the isMapppedIdentity in all parsers to reduce the amount of objects transferred
- sync model field to shopware's supplierNumber

### Fixed
- product translations

### Removed
- variation packaging unit

## [2.0.0-rc1]
### Added
- complete rewrite using the plenty rest api
