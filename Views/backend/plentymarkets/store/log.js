// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Log}

/**
 * The log store is used to load the log model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Log', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Log',
	batch: true,
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getLog}'
		},
		reader: {
			type: 'json',
			root: 'data',
			totalProperty: 'total'
		}
	}
});
// {/block}
