// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Producer}

/**
 * The producer store is used to load the producer model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Producer', {
	extend: 'Ext.data.Store',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getProducerList}',
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	},
	model: 'Shopware.apps.Plentymarkets.model.Producer'
});
// {/block}
