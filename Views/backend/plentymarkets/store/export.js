// {namespace name=backend/Plentymarkets/store}
// {block name=backend/Plentymarkets/store/Export}

/**
 * The export store is used to load, create and update the export data and is extended by the Ext data store "Ext.data.Store". 
 * With Ext stores you can handle model data like adding, getting and removing models in a defined store.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
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
			update: '{url action=handleExport}'
        },
		reader: {
			type: 'json',
			root: 'data'
		}
	}
});
// {/block}
