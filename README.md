![plentymarkets Logo](http://www.plentymarkets.eu/layout/pm/images/logo/plentymarkets-logo.jpg)

# PlentyConnector

* **License:** MIT
* **Repository:** [Github](https://github.com/plentymarkets/plentymarkets-shopware-connector)
* **Documentation:** [Google Docs](https://docs.google.com/document/d/1yPr_SrBshsT0IJSuI8DmA7Berr275ZY6qDMMiqavbQI/edit?usp=sharing)

## Requirements

* plentymarkets version >= 7.0
* shopware version >= 5.2
* shell access
* cronjobs
* active plentymarkets webshop
* plentymarkets user with all rest permissions

## Installation Guide

### Git

**Prepare Plugin**
* cd Shopware/custom/plugins
* git clone https://github.com/plentymarkets/plentymarkets-shopware-connector.git PlentyConnector
* cd PlentyConnector
* composer install --no-dev

**Install Plugin**
* cd Shopware
* ./bin/console sw:plugin:refresh
* ./bin/console sw:plugin:install --activate PlentyConnector
* ./var/cache/clear_cache.sh

**Configure Plugin**
* visit yourshopwaredomain/backend
* open Settings > PlentyConnector
* add and test api creddentials
* complete all necessary mappings

