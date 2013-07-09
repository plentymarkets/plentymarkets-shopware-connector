// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Referrer}
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
