// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/dx/Continuous}
Ext.define('Shopware.apps.Plentymarkets.store.dx.Continuous', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.dx.Continuous',
	batch: true,
	storeId: 'plentymarkets-store-dx-continuous',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getDxContinuous}'
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
