// {namespace name=backend/Plentymarkets/model}
// {block name=backend/Plentymarkets/model/Export}

/**
 * The export data model defines the different data fields for data export and
 * is extended by the Ext data model "Ext.data.Model".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.model.Export', {

	extend: 'Ext.data.Model',

	fields: [
	// {block name="backend/Plentymarkets/model/Export/fields"}{/block}
	{
		name: 'ExportStatus',
		type: 'string'
	}, {
		name: 'ExportTimestampStart',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ExportTimestampFinished',
		type: 'date',
		dateFormat: 'timestamp'
	}, {
		name: 'ExportLastErrorMessage',
		type: 'string'
	}, {
		name: 'ExportQuantity',
		type: 'integer'
	}, {
		name: 'ExportEntityName',
		type: 'string'
	}, {
		name: 'ExportEntityDesription',
		type: 'string'
	}, {
		name: 'ExportAction',
		type: 'string'
	}],

	proxy: {
		type: 'ajax',

		api: {
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
