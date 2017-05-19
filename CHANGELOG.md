# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- Order status and shipping number import functionality
- display mapping console command
- Category metaRobots are synced to a shopware attribute
- shipping costs vatRate is calculated now
- Added reference unit synchronization
- Sync product packaging units and content

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

### Fixed
- product translations

### Removed
- Variation packaging unit

## [2.0.0-rc1]
### Added
- complete rewrite using the plenty rest api
