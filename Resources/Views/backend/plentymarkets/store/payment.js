// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Payment}

/**
 * The payment store is used to load the payment model data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.store.Payment', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Payment',
	batch: true,
	storeId: 'plentymarkets-store-payment',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getPaymentList}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
