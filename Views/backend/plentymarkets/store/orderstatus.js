// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Orderstatus}

/**
 * The order status store is used to load the order status model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Orderstatus', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Orderstatus',
	storeId: 'plentymarkets-store-ordetstatus',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getOrderstatusList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
