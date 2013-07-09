// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Warehouse}
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
