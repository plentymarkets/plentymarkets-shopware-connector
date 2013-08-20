// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Referrer}

/**
 * The referrer store is used to load the referrer model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Referrer', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Referrer',
	batch: true,
	storeId: 'plentymarkets-store-referrer',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getReferrerList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
