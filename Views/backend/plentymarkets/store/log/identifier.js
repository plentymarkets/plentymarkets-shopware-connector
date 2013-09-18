// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/log/Identifier}

/**
 * The log store is used to load the log model data and is extended by the Ext
 * data store "Ext.data.Store". With Ext stores you can handle model data like
 * adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.log.Identifier', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.log.Identifier',
	batch: true,
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getLogIdentifierList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
