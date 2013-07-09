// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Multishop}
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
