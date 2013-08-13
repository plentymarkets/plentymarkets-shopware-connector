// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Warehouse}

/**
 * The warehouse store is used to load the warehouse model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Warehouse', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Warehouse',
	batch: true,
	storeId: 'plentymarkets-store-warehouse',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getWarehouseList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
