// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Multishop}

/**
 * The multishop store is used to load multishop model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Multishop', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Multishop',
	batch: true,
	storeId: 'plentymarkets-store-multishop',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getMultishopList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
