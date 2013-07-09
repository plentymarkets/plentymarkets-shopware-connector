// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Export}
Ext.define('Shopware.apps.Plentymarkets.store.Export', {
	extend: 'Ext.data.Store',
	model: 'Shopware.apps.Plentymarkets.model.Export',
	batch: true,
	storeId: 'plentymarkets-store-export',
	proxy: {
		type: 'ajax',
		api: {
			read: '{url action=getExportStatusList}',
			create: '{url action=handleExport}',
			update: '{url action=handleExport}',
		},
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
