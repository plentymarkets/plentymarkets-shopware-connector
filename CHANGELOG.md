# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased]
### Fixed
- price configurations with multiple selected customer groups
- use correct shop id for orders

### Changed
- remove media on update to prevent thumbnail corruption
- only update media files if file hash changes
- use array syntax for order querys

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
