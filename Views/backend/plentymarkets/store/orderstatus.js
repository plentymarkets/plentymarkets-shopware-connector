// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Orderstatus}
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
